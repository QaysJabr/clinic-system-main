<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\AuditLogger;
use App\Support\ClinicSettings;
use App\Support\Excel\ClinicExcelWorkbook;
use App\Support\Excel\ClinicExportMeta;
use App\Support\Excel\XlsxExportResponse;
use App\Support\PatientLedgerService;
use App\Support\PatientProfileService;
use App\Support\ReportViewMeta;
use App\Support\ClinicPdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientAccountController extends Controller
{
    use AuthorizesRequests;

    public function profile(Request $request, Patient $patient)
    {
        $this->authorize('viewProfile', $patient);

        $filters = PatientProfileService::filtersFromRequest($request);
        $canViewClinical = Gate::allows('viewPatientClinical', $patient);

        $visits = $canViewClinical
            ? PatientProfileService::visitsPaginator($patient, $filters, $request)
            : PatientProfileService::emptyPaginator($request, 'visits_page');

        $appointments = $canViewClinical
            ? PatientProfileService::appointmentsPaginator($patient, $filters, $request)
            : PatientProfileService::emptyPaginator($request, 'appointments_page');

        $invoices = PatientProfileService::invoicesPaginator($patient, $filters, $request);
        $payments = PatientProfileService::paymentsPaginator($patient, $filters, $request);

        $summary = PatientProfileService::financialSummary($patient, $filters);

        $ledger = PatientLedgerService::buildFromFilters($patient, $filters);
        $statementTotals = PatientLedgerService::statementTotals($ledger);

        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = $this->reportMetaForPatient($patient, $filters);
        $pageTitle = __('patients.page_title_profile', ['name' => $patient->full_name]);

        $viewData = compact(
            'patient', 'filters', 'canViewClinical', 'visits', 'appointments', 'invoices', 'payments',
            'summary', 'ledger', 'statementTotals', 'doctors', 'clinic', 'cur', 'reportMeta', 'pageTitle'
        );

        if ($request->ajax()) {
            return view('patients.profile-content', $viewData);
        }

        return view('patients.profile', $viewData);
    }

    public function profilePrint(Request $request, Patient $patient)
    {
        $this->authorize('viewProfile', $patient);

        AuditLogger::log('export', 'patients', (string) $patient->id, __('patients.audit_export_profile_print'), null, null);

        $filters = PatientProfileService::filtersFromRequest($request);
        $canViewClinical = Gate::allows('viewPatientClinical', $patient);

        $payload = $this->profileExportCollections($patient, $filters, $canViewClinical);
        $summary = PatientProfileService::financialSummary($patient, $filters);
        $ledger = PatientLedgerService::buildFromFilters($patient, $filters);
        $statementTotals = PatientLedgerService::statementTotals($ledger);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = $this->reportMetaForPatient($patient, $filters);

        return view('patients.profile-print', array_merge($payload, [
            'patient' => $patient,
            'summary' => $summary,
            'ledger' => $ledger,
            'statementTotals' => $statementTotals,
            'clinic' => $clinic,
            'cur' => $cur,
            'filters' => $filters,
            'canViewClinical' => $canViewClinical,
            'reportMeta' => $reportMeta,
            'generatedAt' => now(),
            'backUrl' => route('patients.profile', array_merge(['patient' => $patient->id], $request->query())),
        ]));
    }

    public function profilePdf(Request $request, Patient $patient)
    {
        $this->authorize('viewProfile', $patient);

        AuditLogger::log('export', 'patients', (string) $patient->id, __('patients.audit_export_profile_pdf'), null, null);

        $filters = PatientProfileService::filtersFromRequest($request);
        $canViewClinical = Gate::allows('viewPatientClinical', $patient);
        $payload = $this->profileExportCollections($patient, $filters, $canViewClinical);
        $summary = PatientProfileService::financialSummary($patient, $filters);
        $ledger = PatientLedgerService::buildFromFilters($patient, $filters);
        $statementTotals = PatientLedgerService::statementTotals($ledger);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = $this->reportMetaForPatient($patient, $filters);

        return ClinicPdf::download('patients.profile-print', array_merge($payload, [
            'patient' => $patient,
            'summary' => $summary,
            'ledger' => $ledger,
            'statementTotals' => $statementTotals,
            'clinic' => $clinic,
            'cur' => $cur,
            'filters' => $filters,
            'canViewClinical' => $canViewClinical,
            'reportMeta' => $reportMeta,
            'generatedAt' => now(),
        ]), 'patient-profile-'.$patient->id.'-'.now()->format('Y-m-d_His').'.pdf');
    }

    public function statement(Request $request, Patient $patient)
    {
        $this->authorize('viewProfile', $patient);

        $filters = PatientProfileService::filtersFromRequest($request);
        $ledger = PatientLedgerService::buildFromFilters($patient, $filters);
        $statementTotals = PatientLedgerService::statementTotals($ledger);

        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = $this->reportMetaForPatient($patient, $filters);
        $pageTitle = __('patients.page_title_statement');

        $viewData = compact(
            'patient', 'ledger', 'filters', 'statementTotals', 'doctors', 'clinic', 'cur', 'reportMeta', 'pageTitle'
        );

        if ($request->ajax()) {
            return view('patients.statement-content', $viewData);
        }

        return view('patients.statement', $viewData);
    }

    public function statementPrint(Request $request, Patient $patient)
    {
        $this->authorize('viewProfile', $patient);

        $filters = PatientProfileService::filtersFromRequest($request);
        $ledger = PatientLedgerService::buildFromFilters($patient, $filters);
        $statementTotals = PatientLedgerService::statementTotals($ledger);
        $clinic = ClinicSettings::current();
        $cur = $clinic->currency ?? '';
        $reportMeta = $this->reportMetaForPatient($patient, $filters);
        $generatedAt = now();
        $backUrl = route('patients.statement', array_merge(['patient' => $patient->id], $request->query()));

        return view('patients.statement-print', compact(
            'patient', 'ledger', 'filters', 'statementTotals', 'clinic', 'cur', 'reportMeta', 'generatedAt', 'backUrl'
        ));
    }

    public function statementPdf(Request $request, Patient $patient)
    {
        $this->authorize('viewProfile', $patient);

        AuditLogger::log('export', 'patients', (string) $patient->id, __('patients.audit_export_statement_pdf'), null, null);

        return ClinicPdf::download(
            'patients.statement-print',
            $this->statementPayload($request, $patient),
            'patient-statement-'.$patient->id.'-'.now()->format('Y-m-d_His').'.pdf'
        );
    }

    public function statementExcel(Request $request, Patient $patient): StreamedResponse
    {
        $this->authorize('viewProfile', $patient);

        AuditLogger::log('export', 'patients', (string) $patient->id, __('patients.audit_export_statement_excel'), null, null);

        $payload = $this->statementPayload($request, $patient);
        $filename = 'patient-statement-'.$patient->id.'-'.now()->format('Y-m-d_His').'.xlsx';

        return XlsxExportResponse::stream($filename, function (Writer $writer) use ($payload, $patient, $request): void {
            $meta = ClinicExportMeta::make(
                __('patients.meta_report_title', ['name' => $patient->full_name]),
                $this->statementExcelFilterLines($request),
            );
            $wb = new ClinicExcelWorkbook($writer, $meta);

            $headers = [
                __('patients.excel_header_date'),
                __('patients.excel_header_type'),
                __('patients.excel_header_ref'),
                __('patients.excel_header_description'),
                __('patients.excel_header_debit'),
                __('patients.excel_header_credit'),
                __('patients.excel_header_balance'),
            ];
            $sheet = $wb->beginTableSheet(
                __('patients.excel_sheet_statement'),
                $patient->full_name,
                $headers,
            );
            $i = 0;
            foreach ($payload['ledger'] as $row) {
                $wb->writeDataRow([
                    $row['date'],
                    $row['type'],
                    $row['ref'],
                    $row['description'],
                    $row['debit'] ?? '—',
                    $row['credit'] ?? '—',
                    $row['balance'],
                ], $i++);
            }
            $wb->finishCurrentTable($sheet, count($headers));

            $t = $payload['statementTotals'];
            $summarySheet = $wb->addSheet(__('patients.excel_sheet_statement_summary'));
            $wb->writeBanner(__('patients.excel_sheet_statement_summary'), 2);
            $wb->prepareKeyValueSheet($summarySheet);
            $wb->writeKeyValueBlock([
                [__('patients.excel_row_total_invoiced'), $t['total_invoiced']],
                [__('patients.excel_row_total_paid'), $t['total_paid']],
                [__('patients.excel_row_ending_balance'), $t['ending_balance']],
            ]);
        });
    }

    /**
     * @return list<string>
     */
    private function statementExcelFilterLines(Request $request): array
    {
        $from = $request->date('date_from');
        $to = $request->date('date_to');
        $label = ReportViewMeta::dateRangeLabel($from, $to);

        return $label !== '' ? [$label] : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function statementPayload(Request $request, Patient $patient): array
    {
        $filters = PatientProfileService::filtersFromRequest($request);
        $ledger = PatientLedgerService::buildFromFilters($patient, $filters);
        $statementTotals = PatientLedgerService::statementTotals($ledger);
        $clinic = ClinicSettings::current();

        return [
            'patient' => $patient,
            'ledger' => $ledger,
            'statementTotals' => $statementTotals,
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'reportMeta' => $this->reportMetaForPatient($patient, $filters),
            'generatedAt' => now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{visits: Collection, appointments: Collection, invoices: Collection, payments: Collection}
     */
    private function profileExportCollections(Patient $patient, array $filters, bool $canViewClinical): array
    {
        $limit = 500;

        $visits = $canViewClinical
            ? PatientProfileService::visitBaseQuery($patient, $filters)
                ->with(['doctor:id,full_name', 'appointment:id,appointment_date,status', 'invoices' => fn ($q) => $q->select(['id', 'visit_id', 'invoice_number', 'total', 'paid', 'status'])->orderByDesc('id')->limit(3)])
                ->orderByDesc('visit_date')
                ->limit($limit)
                ->get()
            : collect();

        $appointments = $canViewClinical
            ? PatientProfileService::appointmentBaseQuery($patient, $filters)
                ->with(['doctor:id,full_name'])
                ->orderByDesc('appointment_date')
                ->limit($limit)
                ->get()
            : collect();

        $invoices = PatientProfileService::invoiceBaseQuery($patient, $filters)
            ->with(['treatingDoctor:id,full_name', 'visit:id,visit_date'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $payments = PatientProfileService::paymentBaseQuery($patient, $filters)
            ->with(['invoice:id,invoice_number'])
            ->orderByDesc('payment_date')
            ->limit($limit)
            ->get();

        return compact('visits', 'appointments', 'invoices', 'payments');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{title: string, date_range_label: string, filter_lines: list<string>}
     */
    private function reportMetaForPatient(Patient $patient, array $filters): array
    {
        $lines = [];
        if ($filters['doctor_id']) {
            $lines[] = __('patients.meta_doctor', [
                'name' => optional(Doctor::query()->find($filters['doctor_id']))->full_name ?? '#'.$filters['doctor_id'],
            ]);
        }
        if ($filters['invoice_status']) {
            $statusText = match ($filters['invoice_status']) {
                'paid' => __('patients.invoice_status_paid'),
                'partial' => __('patients.invoice_status_partial'),
                'unpaid' => __('patients.invoice_status_unpaid'),
                default => (string) $filters['invoice_status'],
            };
            $lines[] = __('patients.meta_invoice_status', ['status' => $statusText]);
        }
        if ($filters['visit_status']) {
            $statusText = match ($filters['visit_status']) {
                Visit::STATUS_WAITING => __('visits.status_waiting'),
                Visit::STATUS_IN_PROGRESS => __('visits.status_in_progress'),
                Visit::STATUS_COMPLETED => __('visits.status_completed'),
                Visit::STATUS_CANCELLED => __('visits.status_cancelled'),
                default => (string) $filters['visit_status'],
            };
            $lines[] = __('patients.meta_visit_status', ['status' => $statusText]);
        }
        if ($filters['appointment_status']) {
            $statusText = match ($filters['appointment_status']) {
                'scheduled' => __('patients.appointment_status_scheduled'),
                'completed' => __('patients.appointment_status_completed'),
                'cancelled' => __('patients.appointment_status_cancelled'),
                default => (string) $filters['appointment_status'],
            };
            $lines[] = __('patients.meta_appointment_status', ['status' => $statusText]);
        }

        return [
            'title' => __('patients.meta_report_title', ['name' => $patient->full_name]),
            'date_range_label' => ReportViewMeta::dateRangeLabel($filters['date_from'], $filters['date_to']),
            'filter_lines' => array_values(array_filter($lines)),
        ];
    }
}
