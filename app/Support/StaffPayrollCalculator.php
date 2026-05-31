<?php

namespace App\Support;

use App\Enums\StaffCompensationModel;
use App\Models\StaffCompensationProfile;
use Carbon\CarbonInterface;

/**
 * مقترحات مبالغ حسب ملف التعويض والفترة (توسعة لاحقة للتوليد التلقائي).
 */
final class StaffPayrollCalculator
{
    /**
     * مقترح مبلغ الدفعة حسب ملف التعويض والفترة (إن وُجدت).
     * النسبة المئوية ترجع null حتى يُربط الاحتساب بمصدر إيرادات لاحقاً.
     */
    public static function suggestedAmount(
        ?StaffCompensationProfile $profile,
        ?CarbonInterface $periodStart,
        ?CarbonInterface $periodEnd
    ): ?string {
        if (! $profile || $profile->status !== 'active') {
            return null;
        }

        return match ($profile->compensation_type) {
            StaffCompensationModel::Fixed => self::fixedAmount($profile),
            StaffCompensationModel::Daily => self::dailyTotal($profile, $periodStart, $periodEnd),
            StaffCompensationModel::Percentage => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function buildSnapshot(
        StaffCompensationModel $modelUsed,
        ?StaffCompensationProfile $profile,
        ?CarbonInterface $periodStart,
        ?CarbonInterface $periodEnd,
        string $enteredAmount
    ): ?array {
        $suggested = self::suggestedAmount($profile, $periodStart, $periodEnd);

        return [
            'compensation_type' => $modelUsed->value,
            'entered_amount' => $enteredAmount,
            'suggested_amount' => $suggested,
            'period_start' => $periodStart?->toDateString(),
            'period_end' => $periodEnd?->toDateString(),
            'profile_id' => $profile?->id,
            'amount_excerpt' => $profile ? self::amountExcerpt($profile) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function amountExcerpt(StaffCompensationProfile $profile): array
    {
        return match ($profile->compensation_type) {
            StaffCompensationModel::Fixed => [
                'base_salary' => $profile->base_salary !== null ? (string) $profile->base_salary : null,
                'payment_cycle' => $profile->payment_cycle->value,
            ],
            StaffCompensationModel::Percentage => [
                'percentage_rate' => $profile->percentage_rate !== null ? (string) $profile->percentage_rate : null,
                'calculation_basis' => $profile->calculation_basis,
            ],
            StaffCompensationModel::Daily => [
                'daily_wage' => $profile->daily_wage !== null ? (string) $profile->daily_wage : null,
                'payment_cycle' => $profile->payment_cycle->value,
            ],
        };
    }

    private static function fixedAmount(StaffCompensationProfile $profile): ?string
    {
        $v = $profile->base_salary;
        if ($v === null) {
            return null;
        }

        return number_format((float) $v, 2, '.', '');
    }

    private static function dailyTotal(
        StaffCompensationProfile $profile,
        ?CarbonInterface $periodStart,
        ?CarbonInterface $periodEnd
    ): ?string {
        if (! $periodStart || ! $periodEnd) {
            return null;
        }

        $rate = (float) ($profile->daily_wage ?? 0);
        if ($rate <= 0) {
            return null;
        }

        $days = (int) $periodStart->diffInDays($periodEnd) + 1;

        return number_format($rate * $days, 2, '.', '');
    }
}
