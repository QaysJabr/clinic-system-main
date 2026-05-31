<?php

namespace App\Enums;

/**
 * نماذج التعويض المدعومة حالياً؛ يمكن إضافة قيم لاحقاً دون كسر العقود القديمة.
 */
enum StaffCompensationModel: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case Daily = 'daily';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => __('staff.comp_fixed'),
            self::Percentage => __('staff.comp_percentage'),
            self::Daily => __('staff.comp_daily'),
        };
    }

    public function labelAr(): string
    {
        return $this->label();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Fixed => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300',
            self::Percentage => 'bg-violet-100 text-violet-800 dark:bg-violet-950/50 dark:text-violet-300',
            self::Daily => 'bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300',
        };
    }
}
