<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Support\AuditLogger;
use App\Support\ClinicFinancialStats;
use App\Support\ClinicPermissions;
use App\Support\ClinicSettings;
use App\Support\Excel\ClinicExcelWorkbook;
use App\Support\Excel\ClinicExportMeta;
use App\Support\Excel\XlsxExportResponse;
use App\Support\ReportViewMeta;
use App\Support\ClinicPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceivablesReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $doctorId = $request->filled('doctor_id') ? $request->integer('doctor_id') : null;

        $base = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId));

        $totalReceivables = (float) (clone $base)->sum(DB::raw('total - paid'));
        $receivableStats = [
            'total' => $totalReceivables,
            'count' => (clone $base)->count(),
            'unpaid' => (clone $base)->where('status', 'unpaid')->count(),
            'partial' => (clone $base)->where('status', 'partial')->count(),
        ];

        $user = $request->user();
        $showInventoryReports = $user !== null
            && ($user->can(ClinicPermissions::VIEW_INVENTORY) || $user->can(ClinicPermissions::MANAGE_INVENTORY));

        $invoices = (clone $base)
            ->with(['patient:id,full_name', 'treatingDoctor:id,full_name'])
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = [
            'title' => __('invoices.receivables_meta_title'),
            'date_range_label' => ReportViewMeta::dateRangeLabel($from, $to),
            'filter_lines' => array_filter([
                $doctorId ? __('invoices.receivables_filter_doctor', [
                    'name' => optional(Doctor::query()->find($doctorId))->full_name ?? '#'.$doctorId,
                ]) : null,
            ]),
        ];

        $pageTitle = __('invoices.receivables_page_title');

        if ($request->ajax()) {
            return view('reports.receivables-content', compact(
                'invoices', 'totalReceivables', 'receivableStats', 'doctors', 'clinic', 'cur', 'reportMeta', 'pageTitle', 'showInventoryReports'
            ));
        }

        return view('reports.receivables', compact(
            'invoices', 'totalReceivables', 'receivableStats', 'doctors', 'clinic', 'cur', 'reportMeta', 'pageTitle', 'showInventoryReports'
        ));
    }

    public function pdf(Request $request)
    {
        $this->authorizeReceivables($request);

        AuditLogger::log('export', 'reports', null, __('invoices.audit_receivables_pdf'), null, null);

        $payload = $this->receivablesPayload($request);

        return ClinicPdf::download(
            'reports.receivables-pdf',
            $payload,
            'receivables-'.now()->format('Y-m-d_His').'.pdf'
        );
    }

    public function excel(Request $request): StreamedResponse
    {
        $this->authorizeReceivables($request);

        AuditLogger::log('export', 'reports', null, __('invoices.audit_receivables_excel'), null, null);

        $payload = $this->receivablesPayload($request);
        $filename = 'receivables-'.now()->format('Y-m-d_His').'.xlsx';

        return XlsxExportResponse::stream($filename, function (Writer $writer) use ($payload, $request): void {
            $meta = ClinicExportMeta::make(
                __('invoices.receivables_meta_title'),
                $this->receivablesExcelFilterLines($request),
            );
            $wb = new ClinicExcelWorkbook($writer, $meta);

            $headers = [
                __('invoices.field_patient'),
                __('invoices.field_invoice_number'),
                __('invoices.field_doctor'),
                __('invoices.field_total'),
                __('invoices.field_paid'),
                __('invoices.field_remaining'),
                __('invoices.field_status'),
                __('validation.attributes.due_date'),
                __('invoices.field_created_at'),
            ];
            $sheet = $wb->beginTableSheet(
                __('invoices.receivables_excel_sheet_receivables'),
                __('invoices.receivables_meta_title'),
                $headers,
            );
            $i = 0;
            foreach ($payload['invoices'] as $inv) {
                $remaining = round((float) $inv->total - (float) $inv->paid, 2);
                $wb->writeDataRow([
                    optional($inv->patient)->full_name ?? '—',
                    $inv->invoice_number,
                    optional($inv->treatingDoctor)->full_name ?? '—',
                    round((float) $inv->total, 2),
                    round((float) $inv->paid, 2),
                    $remaining,
                    $inv->status === 'partial' ? __('common.partial') : __('common.unpaid'),
                    $inv->due_date?->format('d/m/Y') ?? '—',
                    $inv->created_at?->format('d/m/Y') ?? '—',
                ], $i++);
            }
            $wb->finishCurrentTable($sheet, count($headers));

            $summarySheet = $wb->addSheet(__('invoices.receivables_excel_sheet_summary'));
            $wb->writeBanner(__('invoices.receivables_excel_sheet_summary'), 2);
            $wb->prepareKeyValueSheet($summarySheet);
            $wb->writeKeyValueBlock([
                [__('invoices.receivables_excel_row_total_matching'), round((float) $payload['totalReceivables'], 2)],
                [__('invoices.receivables_excel_row_ar_reference'), round((float) $payload['clinicArReference'], 2)],
                [__('invoices.receivables_excel_row_accrual_reference'), round((float) $payload['totalAccrualRevenue'], 2)],
                [__('invoices.receivables_excel_row_patient_cash_reference'), round((float) $payload['totalPatientCashIn'], 2)],
                [__('invoices.receivables_excel_row_doctor_share_reference'), round((float) $payload['totalDoctorShareExpense'], 2)],
                [__('invoices.receivables_excel_row_net_profit_reference'), round((float) $payload['netProfit'], 2)],
            ]);
        });
    }

    /**
     * @return list<string>
     */
    private function receivablesExcelFilterLines(Request $request): array
    {
        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $lines = [];
        $range = ReportViewMeta::dateRangeLabel($from, $to);
        if ($range !== '') {
            $lines[] = $range;
        }
        if ($request->filled('doctor_id')) {
            $doctor = Doctor::find($request->integer('doctor_id'));
            if ($doctor) {
                $lines[] = __('invoices.receivables_filter_doctor', ['name' => $doctor->full_name]);
            }
        }

        return $lines;
    }

    private function authorizeReceivables(Request $request): void
    {
        if (! $request->user()?->can('view reports')) {
            abort(403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function receivablesPayload(Request $request): array
    {
        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $doctorId = $request->filled('doctor_id') ? $request->integer('doctor_id') : null;

        $base = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId));

        $totalReceivables = (float) (clone $base)->sum(DB::raw('total - paid'));

        $invoices = (clone $base)
            ->with(['patient:id,full_name', 'treatingDoctor:id,full_name'])
            ->orderByDesc('created_at')
            ->get();

        $clinic = ClinicSettings::current();
        $reportMeta = [
            'title' => __('invoices.receivables_meta_title'),
            'date_range_label' => ReportViewMeta::dateRangeLabel($from, $to),
            'filter_lines' => array_filter([
                $doctorId ? __('invoices.receivables_filter_doctor', [
                    'name' => optional(Doctor::query()->find($doctorId))->full_name ?? '#'.$doctorId,
                ]) : null,
            ]),
        ];

        return [
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'invoices' => $invoices,
            'totalReceivables' => $totalReceivables,
            'reportMeta' => $reportMeta,
            'generatedAt' => now(),
            'clinicArReference' => ClinicFinancialStats::totalAccountsReceivable(),
            'totalAccountsReceivable' => ClinicFinancialStats::totalAccountsReceivable(),
            'totalAccrualRevenue' => ClinicFinancialStats::totalAccrualRevenue(),
            'totalPatientCashIn' => ClinicFinancialStats::totalPatientCashIn(),
            'totalDoctorShareExpense' => ClinicFinancialStats::totalDoctorShareExpense(),
            'totalExpensesAll' => ClinicFinancialStats::totalExpenses(),
            'totalPayrollPaid' => ClinicFinancialStats::totalPayrollPaid(),
            'netProfit' => ClinicFinancialStats::netProfit(),
        ];
    }
}
