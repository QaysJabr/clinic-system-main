<?php

namespace App\Services\Scheduling;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Support\Scheduling\AppointmentTimeRange;
use Carbon\Carbon;

/**
 * FullCalendar event payloads and drag-resize updates.
 */
final class AppointmentCalendarService
{
    public function __construct(
        private readonly AppointmentConflictService $conflicts,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function events(
        string $start,
        string $end,
        ?int $doctorId = null,
        ?int $clinicId = null,
    ): array {
        $query = Appointment::query()
            ->with(['patient:id,full_name', 'doctor:id,full_name'])
            ->whereBetween('appointment_date', [
                Carbon::parse($start)->toDateString(),
                Carbon::parse($end)->toDateString(),
            ]);

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return $query->orderBy('appointment_date')->orderBy('start_time')->get()
            ->map(fn (Appointment $a) => $this->toEvent($a))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toEvent(Appointment $appointment): array
    {
        $range = AppointmentTimeRange::fromAppointment($appointment);
        $status = AppointmentStatus::tryFrom($appointment->status) ?? AppointmentStatus::Scheduled;

        return [
            'id' => (string) $appointment->id,
            'title' => trim((optional($appointment->patient)->full_name ?? __('common.em_dash'))
                .' · '.(optional($appointment->doctor)->full_name ?? '')),
            'start' => $range->start->toIso8601String(),
            'end' => $range->end->toIso8601String(),
            'backgroundColor' => $status->color(),
            'borderColor' => $status->color(),
            'editable' => ! in_array($appointment->status, [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::NoShow->value,
            ], true),
            'extendedProps' => [
                'status' => $appointment->status,
                'patientId' => $appointment->patient_id,
                'doctorId' => $appointment->doctor_id,
                'editUrl' => route('appointments.edit', $appointment),
            ],
        ];
    }

    public function reschedule(
        Appointment $appointment,
        string $startIso,
        ?string $endIso = null,
    ): Appointment {
        $start = Carbon::parse($startIso);
        $end = $endIso ? Carbon::parse($endIso) : $start->copy()->addMinutes(
            $appointment->duration_minutes ?? config('scheduling.default_slot_minutes', 15)
        );

        $this->conflicts->assertCanBook(
            (int) $appointment->doctor_id,
            $start->toDateString(),
            $start->format('H:i'),
            $end->format('H:i'),
            (int) $start->diffInMinutes($end),
            (int) $appointment->id
        );

        $appointment->forceFill([
            'appointment_date' => $start->toDateString(),
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'duration_minutes' => (int) $start->diffInMinutes($end),
        ])->save();

        return $appointment->fresh(['patient', 'doctor']);
    }
}
