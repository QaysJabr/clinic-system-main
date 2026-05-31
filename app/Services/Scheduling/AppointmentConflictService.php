<?php

namespace App\Services\Scheduling;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailableDate;
use App\Support\Scheduling\AppointmentTimeRange;
use App\Support\Scheduling\SchedulingSettings;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Validates appointments against overlaps, hours, breaks, and blocked dates.
 */
final class AppointmentConflictService
{
    public function assertCanBook(
        int $doctorId,
        string $date,
        string $startTime,
        ?string $endTime,
        ?int $durationMinutes,
        ?int $ignoreAppointmentId = null,
        ?SchedulingSettings $settings = null,
    ): AppointmentTimeRange {
        $settings ??= SchedulingSettings::fromClinicSettings();
        $range = AppointmentTimeRange::fromParts($date, $startTime, $endTime, $durationMinutes);

        $this->assertWithinClinicHours($range, $settings);
        $this->assertDoctorScheduleAllows($doctorId, $date, $range);
        $this->assertNotUnavailable($doctorId, $date, $range, $settings);
        $this->assertNoOverlap($doctorId, $range, $ignoreAppointmentId, $settings);

        return $range;
    }

    public function hasOverlap(
        int $doctorId,
        AppointmentTimeRange $range,
        ?int $ignoreAppointmentId = null,
    ): bool {
        $settings = SchedulingSettings::fromClinicSettings();

        if ($settings->allowOverbooking) {
            return false;
        }

        return $this->findOverlapping($doctorId, $range, $ignoreAppointmentId)->isNotEmpty();
    }

    private function assertWithinClinicHours(AppointmentTimeRange $range, SchedulingSettings $settings): void
    {
        $dayStart = $range->start->copy()->setTimeFromTimeString($settings->dayStart.':00');
        $dayEnd = $range->start->copy()->setTimeFromTimeString($settings->dayEnd.':00');

        if ($range->start->lt($dayStart) || $range->end->gt($dayEnd)) {
            throw ValidationException::withMessages([
                'start_time' => [__('appointments.error_outside_clinic_hours')],
            ]);
        }
    }

    private function assertDoctorScheduleAllows(int $doctorId, string $date, AppointmentTimeRange $range): void
    {
        $dayOfWeek = $range->start->dayOfWeekIso;

        $schedule = DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->with('breaks')
            ->first();

        if ($schedule === null) {
            return;
        }

        $schedStart = $range->start->copy()->setTimeFromTimeString(substr((string) $schedule->start_time, 0, 5).':00');
        $schedEnd = $range->start->copy()->setTimeFromTimeString(substr((string) $schedule->end_time, 0, 5).':00');

        if ($range->start->lt($schedStart) || $range->end->gt($schedEnd)) {
            throw ValidationException::withMessages([
                'start_time' => [__('appointments.error_outside_doctor_schedule')],
            ]);
        }

        foreach ($schedule->breaks as $break) {
            $breakStart = $range->start->copy()->setTimeFromTimeString(substr((string) $break->start_time, 0, 5).':00');
            $breakEnd = $range->start->copy()->setTimeFromTimeString(substr((string) $break->end_time, 0, 5).':00');
            $breakRange = new AppointmentTimeRange($breakStart, $breakEnd);

            if ($range->overlaps($breakRange)) {
                throw ValidationException::withMessages([
                    'start_time' => [__('appointments.error_during_break')],
                ]);
            }
        }
    }

    private function assertNotUnavailable(
        int $doctorId,
        string $date,
        AppointmentTimeRange $range,
        SchedulingSettings $settings,
    ): void {
        $blocks = DoctorUnavailableDate::query()
            ->whereDate('unavailable_date', $date)
            ->where(function ($q) use ($doctorId): void {
                $q->whereNull('doctor_id')
                    ->orWhere('doctor_id', $doctorId);
            })
            ->get();

        foreach ($blocks as $block) {
            if ($block->all_day) {
                throw ValidationException::withMessages([
                    'appointment_date' => [__('appointments.error_day_blocked')],
                ]);
            }

            if ($block->start_time && $block->end_time) {
                $blockStart = $range->start->copy()->setTimeFromTimeString(substr((string) $block->start_time, 0, 5).':00');
                $blockEnd = $range->start->copy()->setTimeFromTimeString(substr((string) $block->end_time, 0, 5).':00');
                $blockRange = new AppointmentTimeRange($blockStart, $blockEnd);

                if ($range->overlaps($blockRange)) {
                    throw ValidationException::withMessages([
                        'start_time' => [__('appointments.error_time_blocked')],
                    ]);
                }
            }
        }
    }

    private function assertNoOverlap(
        int $doctorId,
        AppointmentTimeRange $range,
        ?int $ignoreAppointmentId,
        SchedulingSettings $settings,
    ): void {
        if ($settings->allowOverbooking) {
            return;
        }

        if ($this->findOverlapping($doctorId, $range, $ignoreAppointmentId)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'start_time' => [__('appointments.error_slot_conflict')],
            ]);
        }
    }

    /**
     * @return Collection<int, Appointment>
     */
    private function findOverlapping(int $doctorId, AppointmentTimeRange $range, ?int $ignoreAppointmentId)
    {
        $date = $range->start->toDateString();
        $buffer = SchedulingSettings::fromClinicSettings()->bufferMinutes;

        $appointments = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', AppointmentStatus::blocking())
            ->when($ignoreAppointmentId, fn ($q) => $q->where('id', '!=', $ignoreAppointmentId))
            ->get();

        return $appointments->filter(function (Appointment $existing) use ($range, $buffer): bool {
            try {
                $existingRange = AppointmentTimeRange::fromAppointment($existing);
            } catch (\InvalidArgumentException) {
                return false;
            }

            if ($buffer > 0) {
                $existingRange = new AppointmentTimeRange(
                    $existingRange->start->copy()->subMinutes($buffer),
                    $existingRange->end->copy()->addMinutes($buffer)
                );
            }

            return $range->overlaps($existingRange);
        });
    }
}
