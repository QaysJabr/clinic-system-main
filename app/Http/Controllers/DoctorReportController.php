<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Support\AuditLogger;
use App\Support\ClinicFinancialStats;
use App\Support\ClinicPermissions;
use App\Support\ClinicSettings;
use App\Support\DoctorReportService;
use App\Support\Excel\ClinicExcelWorkbook;
use App\Support\Excel\ClinicExportMeta;
use App\Support\Excel\XlsxExportResponse;
use App\Support\ReportViewMeta;
use App\Support\ClinicPdf;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorReportController extends Controller
{
    public function show(Request $request, Doctor $doctor)
    {
        $this->authorizeDoctorReport($request, $doctor);

        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $stats = DoctorReportService::forDoctor($doctor, $from, $to);
        $invoices = DoctorReportService::invoicesForTable($doctor, $from, $to);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = [
            'title' => __('doctors.meta_report_title', ['name' => $doctor->full_name]),
            'date_range_label' => ReportViewMeta::dateRangeLabel($from, $to),
            'filter_lines' => [],
        ];
        $pageTitle = __('doctors.page_title_report');

        $user = $request->user();
        $showInventoryReports = $user !== null
            && ($user->can(ClinicPermissions::VIEW_INVENTORY) || $user->can(ClinicPermissions::MANAGE_INVENTORY));

        if ($request->ajax()) {
            return view('reports.doctors.show-content', compact(
                'doctor', 'stats', 'invoices', 'clinic', 'cur', 'reportMeta', 'pageTitle', 'showInventoryReports'
            ));
        }

        return view('reports.doctors.show', compact(
            'doctor', 'stats', 'invoices', 'clinic', 'cur', 'reportMeta', 'pageTitle', 'showInventoryReports'
        ));
    }

    public function pdf(Request $request, Doctor $doctor)
    {
        $this->authorizeDoctorReport($request, $doctor);

        AuditLogger::log('export', 'reports', (string) $doctor->id, __('doctors.audit_export_pdf'), null, null);

        $payload = $this->doctorPayload($request, $doctor);

        return ClinicPdf::download(
            'reports.doctors.pdf',
            $payload,
            'doctor-report-'.$doctor->id.'-'.now()->format('Y-m-d_His').'.pdf'
        );
    }

    public function excel(Request $request, Doctor $doctor): StreamedResponse
    {
        $this->authorizeDoctorReport($request, $doctor);

        AuditLogger::log('export', 'reports', (string) $doctor->id, __('doctors.audit_export_excel'), null, null);

        $payload = $this->doctorPayload($request, $doctor);
        $filename = 'doctor-report-'.$doctor->id.'-'.now()->format('Y-m-d_His').'.xlsx';

        return XlsxExportResponse::stream($filename, function (Writer $writer) use ($payload, $doctor, $request): void {
            $meta = ClinicExportMeta::make(
                __('doctors.meta_report_title', ['name' => $doctor->full_name]),
                $this->doctorExcelFilterLines($request),
            );
            $wb = new ClinicExcelWorkbook($writer, $meta);

            $invoiceHeaders = [
                __('doctors.excel_th_date'),
                __('doctors.excel_th_patient'),
                __('doctors.excel_th_invoice'),
                __('doctors.excel_th_total'),
                __('doctors.excel_th_paid'),
                __('doctors.excel_th_doctor_share'),
                __('doctors.excel_th_settlement'),
            ];
            $sheet = $wb->beginTableSheet(
                __('doctors.excel_sheet_invoices'),
                $doctor->full_name,
                $invoiceHeaders,
            );
            $i = 0;
            foreach ($payload['invoices'] as $inv) {
                $earning = $inv->doctorEarnings->first();
                $em = __('common.em_dash');
                $wb->writeDataRow([
                    $inv->created_at?->format('d/m/Y'),
                    optional($inv->patient)->full_name ?? $em,
                    $inv->invoice_number,
                    round((float) $inv->total, 2),
                    round((float) $inv->paid, 2),
                    $earning ? round((float) $earning->earning_amount, 2) : $em,
                    $earning ? ($earning->status === DoctorEarning::STATUS_PAID ? __('doctors.earning_settlement_paid') : __('doctors.earning_settlement_pending')) : $em,
                ], $i++);
            }
            $wb->finishCurrentTable($sheet, count($invoiceHeaders));

            $s = $payload['stats'];
            $summarySheet = $wb->addSheet(__('doctors.excel_sheet_summary'));
            $wb->writeBanner(__('doctors.excel_sheet_summary'), 2);
            $wb->prepareKeyValueSheet($summarySheet);
            $wb->writeKeyValueBlock([
                [__('doctors.excel_row_invoice_count'), $s['invoice_count']],
                [__('doctors.excel_row_accrual'), $s['accrual_revenue']],
                [__('doctors.excel_row_doctor_share'), $s['doctor_share_total']],
                [__('doctors.excel_row_pending'), $s['earnings_pending']],
                [__('doctors.excel_row_paid'), $s['earnings_paid']],
                [__('doctors.excel_row_clinic_accrual_ref'), round((float) $payload['totalAccrualRevenue'], 2)],
                [__('doctors.excel_row_clinic_net_ref'), round((float) $payload['netProfit'], 2)],
            ]);
        });
    }

    /**
     * @return list<string>
     */
    private function doctorExcelFilterLines(Request $request): array
    {
        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $label = ReportViewMeta::dateRangeLabel($from, $to);

        return $label !== '' ? [$label] : [];
    }

    private function authorizeDoctorReport(Request $request, Doctor $doctor): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }
        if ($user->hasRole('admin') || $user->hasRole('accountant') || $user->can('view reports')) {
            return;
        }
        if ($user->hasRole('doctor') && ($user->can(ClinicPermissions::VIEW_DOCTOR_EARNINGS) || $user->can(ClinicPermissions::MANAGE_DOCTOR_EARNINGS))) {
            $linked = $user->linkedDoctor();
            if ($linked && (int) $linked->id === (int) $doctor->id) {
                return;
            }
        }
        abort(403);
    }

    /**
     * @return array<string, mixed>
     */
    private function doctorPayload(Request $request, Doctor $doctor): array
    {
        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $stats = DoctorReportService::forDoctor($doctor, $from, $to);
        $invoices = DoctorReportService::invoicesForTable($doctor, $from, $to);
        $clinic = ClinicSettings::current();

        return [
            'doctor' => $doctor,
            'stats' => $stats,
            'invoices' => $invoices,
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'reportMeta' => [
                'title' => __('doctors.meta_report_title', ['name' => $doctor->full_name]),
                'date_range_label' => ReportViewMeta::dateRangeLabel($from, $to),
                'filter_lines' => [],
            ],
            'generatedAt' => now(),
            'totalAccrualRevenue' => ClinicFinancialStats::totalAccrualRevenue(),
            'totalPatientCashIn' => ClinicFinancialStats::totalPatientCashIn(),
            'totalAccountsReceivable' => ClinicFinancialStats::totalAccountsReceivable(),
            'totalDoctorShareExpense' => ClinicFinancialStats::totalDoctorShareExpense(),
            'totalExpensesAll' => ClinicFinancialStats::totalExpenses(),
            'totalPayrollPaid' => ClinicFinancialStats::totalPayrollPaid(),
            'netProfit' => ClinicFinancialStats::netProfit(),
        ];
    }
}
