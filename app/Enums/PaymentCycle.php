<?php

namespace App\Enums;

/**
 * دورة الدفع للموظف ونوع فترة الراتب في السجلات.
 */
enum PaymentCycle: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => __('payroll.cycle_weekly'),
            self::Monthly => __('payroll.cycle_monthly'),
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
}
