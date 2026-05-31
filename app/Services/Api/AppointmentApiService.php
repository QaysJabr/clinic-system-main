<?php

namespace App\Services\Api;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Services\InAppNotificationService;
use App\Services\Scheduling\AppointmentConflictService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Shared appointment create/update logic for the mobile API (mirrors web controller rules).
 */
final class AppointmentApiService
{
    private const AUDIT_FIELDS = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'reason',
        'notes',
    ];

    public function __construct(
        private readonly AppointmentConflictService $conflicts,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(User $user, array $validated): Appointment
    {
        $this->assertDoctorOwnsSchedule($user, (int) $validated['doctor_id'], 'store');

        $range = $this->conflicts->assertCanBook(
            (int) $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['start_time'],
            $validated['end_time'] ?? null,
            null,
        );

        $payload = array_intersect_key($validated, array_flip(self::AUDIT_FIELDS));
        $payload['start_time'] = $range->start->format('H:i');
        $payload['end_time'] = $range->end->format('H:i');
        $payload['duration_minutes'] = (int) $range->start->diffInMinutes($range->end);

        $appointment = Appointment::create($payload);

        AuditLogger::log(
            'create',
            'appointments',
            $appointment->id,
            __('appointments.audit_create', ['id' => $appointment->id]),
            null,
            $this->snapshot($appointment),
        );

        $appointment->load(['patient', 'doctor']);
        app(InAppNotificationService::class)->pushForAppointmentIfRelevant($appointment);

        return $appointment;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(User $user, Appointment $appointment, array $validated): Appointment
    {
        $this->assertDoctorOwnsSchedule($user, (int) $validated['doctor_id'], 'update');

        $range = $this->conflicts->assertCanBook(
            (int) $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['start_time'],
            $validated['end_time'] ?? null,
            null,
            (int) $appointment->id,
        );

        $old = $this->snapshot($appointment);
        $payload = array_intersect_key($validated, array_flip(self::AUDIT_FIELDS));
        $payload['start_time'] = $range->start->format('H:i');
        $payload['end_time'] = $range->end->format('H:i');
        $payload['duration_minutes'] = (int) $range->start->diffInMinutes($range->end);

        $appointment->update($payload);

        AuditLogger::log(
            'update',
            'appointments',
            $appointment->id,
            __('appointments.audit_update', ['id' => $appointment->id]),
            $old,
            $this->snapshot($appointment->fresh()),
        );

        $appointment->refresh()->load(['patient', 'doctor']);
        app(InAppNotificationService::class)->pushForAppointmentIfRelevant($appointment);

        return $appointment;
    }

    /**
     * @return array<string, string>
     */
    public static function validationRules(?Appointment $existing = null): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => ['required', 'string', 'in:'.implode(',', AppointmentStatus::values())],
            'reason' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    private function assertDoctorOwnsSchedule(User $user, int $doctorId, string $context): void
    {
        if (! $user->hasRole('doctor') || $user->hasRole('admin')) {
            return;
        }

        $doc = $user->linkedDoctor();
        if (! $doc || $doctorId !== (int) $doc->id) {
            $message = $context === 'store'
                ? __('appointments.error_assign_other_doctor_store')
                : __('appointments.error_assign_other_doctor_update');

            throw ValidationException::withMessages(['doctor_id' => $message]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Appointment $appointment): array
    {
        $data = $appointment->only(self::AUDIT_FIELDS);
        if (! empty($data['appointment_date'])) {
            $data['appointment_date'] = $appointment->appointment_date?->format('Y-m-d');
        }

        return $data;
    }
}
