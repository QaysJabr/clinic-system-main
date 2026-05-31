<?php

namespace App\Support\Scheduling;

use App\Models\ClinicSetting;
use Carbon\CarbonInterface;

/**
 * Resolved scheduling configuration for a clinic.
 */
final readonly class SchedulingSettings
{
    public function __construct(
        public int $clinicId,
        public int $slotMinutes,
        public string $dayStart,
        public string $dayEnd,
        public int $bufferMinutes,
        public bool $allowOverbooking,
        public bool $remindersEnabled,
    ) {}

    public static function fromClinicSettings(?ClinicSetting $settings = null): self
    {
        $settings ??= ClinicSetting::current();

        return new self(
            clinicId: (int) $settings->clinic_id,
            slotMinutes: (int) ($settings->scheduling_slot_minutes ?? config('scheduling.default_slot_minutes', 15)),
            dayStart: self::normalizeTime($settings->scheduling_day_start ?? config('scheduling.default_day_start', '09:00')),
            dayEnd: self::normalizeTime($settings->scheduling_day_end ?? config('scheduling.default_day_end', '17:00')),
            bufferMinutes: (int) ($settings->scheduling_buffer_minutes ?? config('scheduling.buffer_minutes', 0)),
            allowOverbooking: (bool) ($settings->scheduling_allow_overbooking ?? config('scheduling.allow_overbooking', false)),
            remindersEnabled: (bool) ($settings->scheduling_reminders_enabled ?? true),
        );
    }

    public static function normalizeTime(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        $str = (string) $value;

        return substr($str, 0, 5);
    }
}
