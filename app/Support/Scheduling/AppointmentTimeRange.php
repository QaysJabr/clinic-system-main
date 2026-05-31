<?php

namespace App\Support\Scheduling;

use App\Models\Appointment;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Normalizes appointment date/time to absolute ranges for conflict checks.
 */
final class AppointmentTimeRange
{
    public function __construct(
        public readonly Carbon $start,
        public readonly Carbon $end,
    ) {}

    public static function fromParts(string $date, string $startTime, ?string $endTime = null, ?int $durationMinutes = null): self
    {
        $start = Carbon::parse($date.' '.self::normalizeHm($startTime));
        $end = $endTime
            ? Carbon::parse($date.' '.self::normalizeHm($endTime))
            : $start->copy()->addMinutes($durationMinutes ?? config('scheduling.default_slot_minutes', 15));

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('Appointment end must be after start.');
        }

        return new self($start, $end);
    }

    public static function fromAppointment(Appointment $appointment): self
    {
        $date = $appointment->appointment_date?->format('Y-m-d');
        if (! $date || ! $appointment->start_time) {
            throw new InvalidArgumentException('Appointment missing date or start time.');
        }

        return self::fromParts(
            $date,
            (string) $appointment->start_time,
            $appointment->end_time ? (string) $appointment->end_time : null,
            $appointment->duration_minutes
        );
    }

    public function overlaps(self $other): bool
    {
        return $this->start->lt($other->end) && $this->end->gt($other->start);
    }

    public static function normalizeHm(string $time): string
    {
        return substr($time, 0, 5);
    }
}
