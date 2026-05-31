<?php

namespace App\Services\Api;

use App\Models\Visit;
use App\Services\DoctorEarningSyncService;
use App\Support\AuditLogger;
use App\Support\ClinicBusinessRules;
use Illuminate\Validation\ValidationException;

/**
 * Mobile API visit mutations (status updates for queue workflow).
 */
final class VisitApiService
{
    /**
     * @return list<string>
     */
    public static function allowedStatuses(): array
    {
        return [
            Visit::STATUS_WAITING,
            Visit::STATUS_IN_PROGRESS,
            Visit::STATUS_COMPLETED,
            Visit::STATUS_CANCELLED,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function clinicalValidationRules(): array
    {
        return [
            'chief_complaint' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateClinical(Visit $visit, array $validated): Visit
    {
        if ($visit->status === Visit::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تعديل ملاحظات زيارة ملغاة',
            ]);
        }

        $old = $this->clinicalSnapshot($visit);
        $visit->update([
            'chief_complaint' => $validated['chief_complaint'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        $visit->refresh();

        AuditLogger::log(
            'update',
            'visits',
            $visit->id,
            __('visits.audit_update', ['id' => $visit->id]),
            $old,
            $this->clinicalSnapshot($visit),
        );

        return $visit;
    }

    public function updateStatus(Visit $visit, string $status): Visit
    {
        if ($status === Visit::STATUS_COMPLETED) {
            $this->assertCanComplete($visit);
        }

        $old = $this->snapshot($visit);
        $visit->update(['status' => $status]);
        $visit->refresh();

        AuditLogger::log(
            'update',
            'visits',
            $visit->id,
            __('visits.audit_update', ['id' => $visit->id]),
            $old,
            $this->snapshot($visit),
        );

        if ($visit->isCompleted()) {
            app(DoctorEarningSyncService::class)->syncInvoicesForVisit((int) $visit->id);
        }

        return $visit;
    }

    private function assertCanComplete(Visit $visit): void
    {
        if (! ClinicBusinessRules::settings()->require_invoice_for_visit) {
            return;
        }

        if (! ClinicBusinessRules::visitHasInvoice((int) $visit->id)) {
            throw ValidationException::withMessages([
                'status' => __('visits.error_complete_visit_without_invoice'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Visit $visit): array
    {
        $data = $visit->only(['patient_id', 'doctor_id', 'appointment_id', 'visit_date', 'status']);
        if (! empty($data['visit_date'])) {
            $data['visit_date'] = $visit->visit_date?->format('Y-m-d');
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function clinicalSnapshot(Visit $visit): array
    {
        return $visit->only(['chief_complaint', 'diagnosis', 'treatment_plan', 'notes']);
    }
}
