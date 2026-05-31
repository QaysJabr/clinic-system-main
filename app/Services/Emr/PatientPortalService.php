<?php

namespace App\Services\Emr;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSetting;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Collection;

final class PatientPortalService
{
    /**
     * @return array{
     *     clinicName: string,
     *     currency: ?string,
     *     upcomingAppointments: Collection<int, Appointment>,
     *     recentAppointments: Collection<int, Appointment>,
     *     openInvoices: Collection<int, Invoice>,
     *     outstandingTotal: float,
     * }
     */
    public function snapshot(Patient $patient): array
    {
        $clinicId = (int) $patient->clinic_id;
        $settings = ClinicSetting::withoutGlobalScopes()->where('clinic_id', $clinicId)->first();
        $clinicName = $settings?->clinic_name
            ?? Clinic::query()->whereKey($clinicId)->value('name')
            ?? (string) config('app.name');

        $inactiveStatuses = [
            AppointmentStatus::Cancelled->value,
            AppointmentStatus::NoShow->value,
        ];

        $upcomingAppointments = Appointment::withoutGlobalScopes()
            ->where('patient_id', $patient->id)
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereNotIn('status', $inactiveStatuses)
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(10)
            ->with(['doctor' => fn ($query) => $query->withoutGlobalScopes()->select(['id', 'full_name'])])
            ->get(['id', 'doctor_id', 'appointment_date', 'start_time', 'end_time', 'status', 'reason']);

        $recentAppointments = Appointment::withoutGlobalScopes()
            ->where('patient_id', $patient->id)
            ->where('appointment_date', '<', now()->toDateString())
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->limit(5)
            ->with(['doctor' => fn ($query) => $query->withoutGlobalScopes()->select(['id', 'full_name'])])
            ->get(['id', 'doctor_id', 'appointment_date', 'start_time', 'end_time', 'status', 'reason']);

        $openInvoices = Invoice::withoutGlobalScopes()
            ->where('patient_id', $patient->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'invoice_number', 'total', 'paid', 'status', 'due_date', 'created_at']);

        $outstandingTotal = (float) $openInvoices->sum(
            fn (Invoice $invoice): float => max(0, (float) $invoice->total - (float) $invoice->paid)
        );

        return [
            'clinicName' => (string) $clinicName,
            'currency' => $settings?->currency,
            'upcomingAppointments' => $upcomingAppointments,
            'recentAppointments' => $recentAppointments,
            'openInvoices' => $openInvoices,
            'outstandingTotal' => $outstandingTotal,
        ];
    }
}
