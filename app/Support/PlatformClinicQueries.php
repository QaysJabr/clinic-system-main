<?php

namespace App\Support;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;

final class PlatformClinicQueries
{
    /**
     * @return Builder<Clinic>
     */
    public static function activeSubscription(): Builder
    {
        return Clinic::query()
            ->where('is_active', true)
            ->where('subscription_status', Clinic::STATUS_ACTIVE)
            ->where(function (Builder $q): void {
                $q->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '>', now());
            });
    }

    /**
     * @return Builder<Clinic>
     */
    public static function expiredSubscription(): Builder
    {
        return Clinic::query()->where(function (Builder $q): void {
            $q->where('subscription_status', Clinic::STATUS_EXPIRED)
                ->orWhere(function (Builder $q2): void {
                    $q2->whereNotNull('subscription_expires_at')
                        ->where('subscription_expires_at', '<=', now());
                });
        });
    }

    /**
     * @return Builder<Clinic>
     */
    public static function expiringWithinDays(int $days): Builder
    {
        return self::activeSubscription()
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [
                now()->startOfDay(),
                now()->addDays($days)->endOfDay(),
            ]);
    }

    /**
     * @return Builder<Clinic>
     */
    public static function suspended(): Builder
    {
        return Clinic::query()->where('is_active', false);
    }
}
