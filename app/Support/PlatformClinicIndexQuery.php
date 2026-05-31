<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PlatformClinicIndexQuery
{
    /**
     * @return Builder<Clinic>
     */
    public static function fromRequest(Request $request): Builder
    {
        $clinics = Clinic::query()
            ->with(['owner:id,name,email', 'plan:id,name'])
            ->withSum([
                'subscriptionPayments as total_paid' => function ($q): void {
                    $q->where('status', SubscriptionPayment::STATUS_SUCCEEDED);
                },
            ], 'amount');

        $search = trim((string) $request->string('q'));
        if ($search !== '') {
            $clinics->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('id', is_numeric($search) ? (int) $search : -1)
                    ->orWhereHas('owner', function ($ownerQ) use ($search): void {
                        $ownerQ->where('email', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $activeFilter = (string) $request->input('is_active', '');
        if ($activeFilter === '1' || $activeFilter === '0') {
            $clinics->where('is_active', $activeFilter === '1');
        }

        $subscriptionFilter = (string) $request->input('subscription_status', '');
        if ($subscriptionFilter === 'expiring_soon') {
            $ids = PlatformClinicQueries::expiringWithinDays(
                (int) config('platform.expiring_soon_days', 7)
            )->pluck('id');
            $clinics->whereIn('id', $ids);
        } elseif ($subscriptionFilter === Clinic::STATUS_EXPIRED) {
            $ids = PlatformClinicQueries::expiredSubscription()->pluck('id');
            $clinics->whereIn('id', $ids);
        } elseif ($subscriptionFilter === Clinic::STATUS_ACTIVE) {
            $ids = PlatformClinicQueries::activeSubscription()->pluck('id');
            $clinics->whereIn('id', $ids);
        }

        return $clinics->orderByDesc('id');
    }
}
