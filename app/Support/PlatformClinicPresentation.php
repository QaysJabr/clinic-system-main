<?php

namespace App\Support;

use App\Models\Clinic;

final class PlatformClinicPresentation
{
    public const TIER_ACTIVE = 'active';

    public const TIER_EXPIRING = 'expiring';

    public const TIER_EXPIRED = 'expired';

    public const TIER_SUSPENDED = 'suspended';

    public static function subscriptionTier(Clinic $clinic): string
    {
        if (! $clinic->is_active) {
            return self::TIER_SUSPENDED;
        }

        $expires = $clinic->subscription_expires_at;

        if ($expires && $expires->isPast()) {
            return self::TIER_EXPIRED;
        }

        if ($clinic->subscription_status === Clinic::STATUS_EXPIRED) {
            return self::TIER_EXPIRED;
        }

        $windowDays = (int) config('platform.expiring_soon_days', 7);

        if ($expires && $expires->isFuture()) {
            $daysLeft = (int) ceil(now()->diffInDays($expires, false));

            if ($daysLeft >= 0 && $daysLeft <= $windowDays) {
                return self::TIER_EXPIRING;
            }
        }

        return self::TIER_ACTIVE;
    }

    public static function tierLabel(string $tier): string
    {
        return match ($tier) {
            self::TIER_EXPIRING => __('platform.status_expiring_soon'),
            self::TIER_EXPIRED => __('platform.status_expired'),
            self::TIER_SUSPENDED => __('platform.status_suspended'),
            default => __('platform.status_active'),
        };
    }

    /**
     * @return string Tailwind classes for status pill
     */
    public static function tierBadgeClass(string $tier): string
    {
        return match ($tier) {
            self::TIER_EXPIRING => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
            self::TIER_EXPIRED => 'bg-rose-100 text-rose-900 dark:bg-rose-950/50 dark:text-rose-200',
            self::TIER_SUSPENDED => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
            default => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
        };
    }
}
