<?php

namespace App\Services\Exports;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\Excel\ClinicExcelWorkbook;
use App\Support\Excel\ClinicExportMeta;
use App\Support\Queries\AppointmentListQuery;
use App\Support\Queries\InvoiceListQuery;
use App\Support\Queries\PatientListQuery;
use App\Support\Queries\VisitListQuery;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer;

final class ExcelExportSheets
{
    public function patients(Writer $writer, Request $request): void
    {
        $wb = $this->workbook($writer, __('patients.title'), $request);

        $headers = [
            __('patients.field_file_number'),
            __('patients.field_full_name'),
            __('patients.field_phone'),
            __('patients.field_date_of_birth'),
            __('patients.field_gender'),
            __('patients.field_national_id'),
            __('patients.field_status'),
            __('patients.excel_header_created_at'),
        ];

        $sheet = $wb->beginTableSheet(
            __('patients.excel_sheet_patients'),
            __('patients.excel_sheet_patients'),
            $headers,
        );

        $patientQuery = PatientListQuery::fromRequest($request);
        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $patientQuery->whereHas('visits', fn ($q) => $q->where('doctor_id', $doc->id));
            } else {
                $patientQuery->whereRaw('1 = 0');
            }
        }

        $i = 0;
        $patientQuery
            ->orderBy('full_name')
            ->cursor()
            ->each(function (Patient $patient) use ($wb, &$i): void {
                $wb->writeDataRow([
                    $patient->file_number,
                    $patient->full_name,
                    $patient->phone,
                    $patient->date_of_birth?->format('d/m/Y'),
                    $this->genderLabel($patient->gender),
                    $patient->national_id,
                    $this->patientStatusLabel($patient->status),
                    $patient->created_at?->format('d/m/Y H:i'),
                ], $i++);
            });

        $wb->finishCurrentTable($sheet, count($headers));
    }

    public function invoices(Writer $writer, Request $request): void
    {
        $wb = $this->workbook($writer, __('invoices.title'), $request);

        $headers = [
            __('invoices.excel_th_created_at'),
            __('invoices.excel_th_patient'),
            __('invoices.excel_th_doctor'),
            __('invoices.excel_th_invoice_number'),
            __('invoices.excel_th_total'),
            __('invoices.excel_th_paid'),
            __('invoices.excel_th_remaining'),
            __('invoices.excel_th_status'),
        ];

        $sheet = $wb->beginTableSheet(
            __('invoices.excel_sheet_name'),
            __('invoices.excel_sheet_name'),
            $headers,
        );

        $i = 0;
        InvoiceListQuery::fromRequest($request)
            ->orderByDesc('created_at')
            ->cursor()
            ->each(function ($invoice) use ($wb, &$i): void {
                $total = (float) $invoice->total;
                $paid = (float) $invoice->paid;
                $wb->writeDataRow([
                    $invoice->created_at?->format('d/m/Y H:i'),
                    optional($invoice->patient)->full_name ?? '—',
                    optional($invoice->treatingDoctor)->full_name ?? '—',
                    $invoice->invoice_number,
                    round($total, 2),
                    round($paid, 2),
                    round($total - $paid, 2),
                    $this->invoiceStatusLabel($invoice->status),
                ], $i++);
            });

        $wb->finishCurrentTable($sheet, count($headers));
    }

    public function appointments(Writer $writer, Request $request): void
    {
        $wb = $this->workbook($writer, __('appointments.title'), $request);

        $headers = [
            __('appointments.excel_th_patient'),
            __('appointments.excel_th_doctor'),
            __('appointments.excel_th_date'),
            __('appointments.excel_th_start'),
            __('appointments.excel_th_end'),
            __('appointments.excel_th_status'),
            __('appointments.excel_th_reason'),
            __('appointments.excel_th_created_at'),
        ];

        $sheet = $wb->beginTableSheet(
            __('appointments.excel_sheet_name'),
            __('appointments.excel_sheet_name'),
            $headers,
        );

        $i = 0;
        AppointmentListQuery::fromRequest($request)
            ->orderByDesc('appointment_date')
            ->orderBy('start_time')
            ->cursor()
            ->each(function (Appointment $appointment) use ($wb, &$i): void {
                $em = __('common.em_dash');
                $wb->writeDataRow([
                    optional($appointment->patient)->full_name ?? $em,
                    optional($appointment->doctor)->full_name ?? $em,
                    $appointment->appointment_date?->format('d/m/Y'),
                    $this->formatTime($appointment->start_time),
                    $this->formatTime($appointment->end_time),
                    $this->appointmentStatusLabel($appointment->status),
                    $appointment->reason,
                    $appointment->created_at?->format('d/m/Y H:i'),
                ], $i++);
            });

        $wb->finishCurrentTable($sheet, count($headers));
    }

    public function visits(Writer $writer, Request $request): void
    {
        $wb = $this->workbook($writer, __('visits.title'), $request);

        $headers = [
            __('visits.excel_th_patient'),
            __('visits.excel_th_doctor'),
            __('visits.excel_th_linked_appointment'),
            __('visits.excel_th_visit_date'),
            __('visits.excel_th_chief_complaint'),
            __('visits.excel_th_status'),
            __('visits.excel_th_diagnosis'),
            __('visits.excel_th_created_at'),
        ];

        $sheet = $wb->beginTableSheet(
            __('visits.excel_sheet_name'),
            __('visits.excel_sheet_name'),
            $headers,
        );

        $visitQuery = VisitListQuery::fromRequest($request);
        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $visitQuery->where('doctor_id', $doc->id);
            } else {
                $visitQuery->whereRaw('1 = 0');
            }
        }

        $i = 0;
        $visitQuery
            ->orderByDesc('visit_date')
            ->cursor()
            ->each(function (Visit $visit) use ($wb, &$i): void {
                $em = __('common.em_dash');
                $apptDate = optional($visit->appointment)->appointment_date?->format('d/m/Y') ?? __('visits.excel_no_appointment');

                $wb->writeDataRow([
                    optional($visit->patient)->full_name ?? $em,
                    optional($visit->doctor)->full_name ?? $em,
                    $apptDate,
                    $visit->visit_date?->format('d/m/Y'),
                    $visit->chief_complaint ?: $em,
                    $this->visitStatusLabel($visit->status),
                    $visit->diagnosis ?: $em,
                    $visit->created_at?->format('d/m/Y H:i'),
                ], $i++);
            });

        $wb->finishCurrentTable($sheet, count($headers));
    }

    private function workbook(Writer $writer, string $reportTitle, Request $request): ClinicExcelWorkbook
    {
        $meta = ClinicExportMeta::make($reportTitle, $this->filterLinesFromRequest($request));

        return new ClinicExcelWorkbook($writer, $meta);
    }

    /**
     * @return list<string>
     */
    private function filterLinesFromRequest(Request $request): array
    {
        $lines = [];
        if ($request->filled('q')) {
            $lines[] = __('common.search').': '.$request->string('q');
        }
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $lines[] = __('common.date_range_between', [
                'from' => $request->date('date_from')?->format('d/m/Y') ?? '…',
                'to' => $request->date('date_to')?->format('d/m/Y') ?? '…',
            ]);
        }
        if ($request->filled('status')) {
            $lines[] = __('common.status').': '.$request->string('status');
        }

        return $lines;
    }

    private function genderLabel(?string $gender): string
    {
        return match ($gender) {
            'male' => __('patients.gender_male'),
            'female' => __('patients.gender_female'),
            default => __('patients.em_dash'),
        };
    }

    private function patientStatusLabel(?string $status): string
    {
        return match ($status) {
            'active' => __('patients.status_active'),
            'inactive' => __('patients.status_inactive'),
            default => (string) $status,
        };
    }

    private function invoiceStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => __('common.paid'),
            'partial' => __('common.partial'),
            'unpaid' => __('common.unpaid'),
            default => (string) $status,
        };
    }

    private function appointmentStatusLabel(?string $status): string
    {
        return match ($status) {
            'scheduled' => __('appointments.status_scheduled'),
            'completed' => __('appointments.status_completed'),
            'cancelled' => __('appointments.status_cancelled'),
            default => (string) $status,
        };
    }

    private function visitStatusLabel(?string $status): string
    {
        return match ($status) {
            Visit::STATUS_WAITING => __('visits.status_waiting'),
            Visit::STATUS_IN_PROGRESS => __('visits.status_in_progress'),
            Visit::STATUS_COMPLETED => __('visits.status_completed'),
            Visit::STATUS_CANCELLED => __('visits.status_cancelled'),
            default => (string) $status,
        };
    }

    private function formatTime(mixed $time): string
    {
        if ($time === null || $time === '') {
            return __('common.em_dash');
        }
        $s = (string) $time;

        return strlen($s) >= 5 ? substr($s, 0, 5) : $s;
    }
}
