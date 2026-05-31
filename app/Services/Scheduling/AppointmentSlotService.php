<?php

namespace App\Services\Scheduling;

use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailableDate;
use App\Support\Scheduling\SchedulingSettings;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Generates bookable time slots for a doctor on a given date.
 */
final class AppointmentSlotService
{
    public function __construct(
        private readonly AppointmentConflictService $conflicts,
    ) {}

    /**
     * @return list<array{start: string, end: string, available: bool}>
     */
    public function slotsForDoctor(int $doctorId, string $date, ?SchedulingSettings $settings = null): array
    {
        $settings ??= SchedulingSettings::fromClinicSettings();
        $day = Carbon::parse($date);

        if ($this->isDayFullyBlocked($doctorId, $day->toDateString())) {
            return [];
        }

        [$windowStart, $windowEnd] = $this->resolveWorkingWindow($doctorId, $day, $settings);

        $slots = [];
        $cursor = $windowStart->copy();
        $slotMinutes = max(5, $settings->slotMinutes);

        while ($cursor->copy()->addMinutes($slotMinutes)->lte($windowEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($slotMinutes);
            $startHm = $cursor->format('H:i');
            $endHm = $slotEnd->format('H:i');

            $available = true;
            try {
                $this->conflicts->assertCanBook(
                    $doctorId,
                    $day->toDateString(),
                    $startHm,
                    $endHm,
                    $slotMinutes
                );
            } catch (ValidationException) {
                $available = false;
            }

            $slots[] = [
                'start' => $startHm,
                'end' => $endHm,
                'available' => $available,
            ];

            $cursor->addMinutes($slotMinutes);
        }

        return $slots;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveWorkingWindow(int $doctorId, Carbon $day, SchedulingSettings $settings): array
    {
        $dayStart = $day->copy()->setTimeFromTimeString($settings->dayStart.':00');
        $dayEnd = $day->copy()->setTimeFromTimeString($settings->dayEnd.':00');

        $schedule = DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $day->dayOfWeekIso)
            ->where('is_active', true)
            ->first();

        if ($schedule) {
            $schedStart = $day->copy()->setTimeFromTimeString(substr((string) $schedule->start_time, 0, 5).':00');
            $schedEnd = $day->copy()->setTimeFromTimeString(substr((string) $schedule->end_time, 0, 5).':00');
            $dayStart = $dayStart->max($schedStart);
            $dayEnd = $dayEnd->min($schedEnd);
        }

        return [$dayStart, $dayEnd];
    }

    private function isDayFullyBlocked(int $doctorId, string $date): bool
    {
        return DoctorUnavailableDate::query()
            ->whereDate('unavailable_date', $date)
            ->where('all_day', true)
            ->where(function ($q) use ($doctorId): void {
                $q->whereNull('doctor_id')->orWhere('doctor_id', $doctorId);
            })
            ->exists();
    }
}
