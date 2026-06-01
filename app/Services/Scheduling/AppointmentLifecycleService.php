<?php

namespace App\Services\Scheduling;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Visit;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Status transitions and visit workflow integration.
 */
final class AppointmentLifecycleService
{
    public function __construct(
        private readonly InAppNotificationService $notifications,
    ) {}

    public function transition(Appointment $appointment, AppointmentStatus $to): Appointment
    {
        $from = AppointmentStatus::tryFrom($appointment->status) ?? AppointmentStatus::Scheduled;

        if (! $this->canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => [__('appointments.error_invalid_status_transition')],
            ]);
        }

        $appointment->status = $to->value;

        if ($to === AppointmentStatus::CheckedIn && $appointment->checked_in_at === null) {
            $appointment->checked_in_at = now();
        }

        if ($to === AppointmentStatus::Cancelled) {
            $this->syncVisitOnCancel($appointment);
        }

        $appointment->save();

        if ($to === AppointmentStatus::Cancelled) {
            $this->notifications->notifyAppointmentCancelled($appointment);
        }

        return $appointment->fresh();
    }

    /**
     * Check-in: creates visit in waiting queue and links appointment.
     */
    public function checkIn(Appointment $appointment): Visit
    {
        return DB::transaction(function () use ($appointment): Visit {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);

            if (in_array($appointment->status, [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
                AppointmentStatus::Completed->value,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => [__('appointments.error_cannot_check_in')],
                ]);
            }

            $visit = $appointment->visit_id
                ? Visit::query()->findOrFail($appointment->visit_id)
                : Visit::query()->create([
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'appointment_id' => $appointment->id,
                    'visit_date' => $appointment->appointment_date,
                    'status' => Visit::STATUS_WAITING,
                ]);

            $appointment->forceFill([
                'visit_id' => $visit->id,
                'status' => AppointmentStatus::CheckedIn->value,
                'checked_in_at' => $appointment->checked_in_at ?? now(),
            ])->save();

            return $visit->fresh(['patient', 'doctor']);
        });
    }

    public function syncFromVisit(Visit $visit): void
    {
        if (! $visit->appointment_id) {
            return;
        }

        $appointment = Appointment::query()->find($visit->appointment_id);
        if (! $appointment) {
            return;
        }

        $status = match ($visit->status) {
            Visit::STATUS_WAITING => AppointmentStatus::CheckedIn,
            Visit::STATUS_IN_PROGRESS => AppointmentStatus::InProgress,
            Visit::STATUS_COMPLETED => AppointmentStatus::Completed,
            Visit::STATUS_CANCELLED => AppointmentStatus::Cancelled,
            default => null,
        };

        if ($status && $appointment->status !== $status->value) {
            $appointment->forceFill([
                'status' => $status->value,
                'visit_id' => $visit->id,
            ])->save();
        }
    }

    private function canTransition(AppointmentStatus $from, AppointmentStatus $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return match ($from) {
            AppointmentStatus::Scheduled => in_array($to, [
                AppointmentStatus::Confirmed,
                AppointmentStatus::CheckedIn,
                AppointmentStatus::Cancelled,
                AppointmentStatus::NoShow,
            ], true),
            AppointmentStatus::Confirmed => in_array($to, [
                AppointmentStatus::CheckedIn,
                AppointmentStatus::Cancelled,
                AppointmentStatus::NoShow,
            ], true),
            AppointmentStatus::CheckedIn => in_array($to, [
                AppointmentStatus::InProgress,
                AppointmentStatus::Cancelled,
                AppointmentStatus::NoShow,
            ], true),
            AppointmentStatus::InProgress => in_array($to, [
                AppointmentStatus::Completed,
                AppointmentStatus::Cancelled,
            ], true),
            AppointmentStatus::Completed, AppointmentStatus::Cancelled, AppointmentStatus::NoShow => false,
        };
    }

    private function syncVisitOnCancel(Appointment $appointment): void
    {
        if (! $appointment->visit_id) {
            return;
        }

        $visit = Visit::query()->find($appointment->visit_id);
        if ($visit && $visit->status === Visit::STATUS_WAITING) {
            $visit->update(['status' => Visit::STATUS_CANCELLED]);
        }
    }
}
