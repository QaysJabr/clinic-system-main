<?php

namespace App\Enums;

/**
 * Appointment lifecycle statuses (stored as string in DB).
 */
enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    /**
     * Statuses that block the calendar slot.
     *
     * @return list<string>
     */
    public static function blocking(): array
    {
        return [
            self::Scheduled->value,
            self::Confirmed->value,
            self::CheckedIn->value,
            self::InProgress->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => '#3B82F6',
            self::Confirmed => '#0F4C81',
            self::CheckedIn => '#8B5CF6',
            self::InProgress => '#F59E0B',
            self::Completed => '#10B981',
            self::Cancelled => '#9CA3AF',
            self::NoShow => '#EF4444',
        };
    }

    public function labelKey(): string
    {
        return 'appointments.status_'.$this->value;
    }
}
