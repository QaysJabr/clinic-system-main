<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use App\Support\PlatformClinicQueries;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class PlatformDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $cached = Cache::remember(
            'platform.dashboard.metrics.v4',
            60,
            fn (): array => $this->computeCachedScalars(),
        );

        return array_merge($cached, $this->computeLiveCollections());
    }

    /**
     * Scalars and plain arrays only — safe for cache serialization.
     *
     * @return array<string, mixed>
     */
    private function computeCachedScalars(): array
    {
        $succeeded = fn () => SubscriptionPayment::query()
            ->where('status', SubscriptionPayment::STATUS_SUCCEEDED);

        $totalRevenue = (float) $succeeded()->sum('amount');

        $monthlyScope = fn () => $succeeded()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year);

        $monthlyRevenue = (float) $monthlyScope()->sum('amount');
        $monthlyRevenueManual = (float) $monthlyScope()
            ->where('source', SubscriptionPayment::SOURCE_MANUAL)
            ->sum('amount');
        $monthlyRevenueStripe = (float) $monthlyScope()
            ->where('source', SubscriptionPayment::SOURCE_STRIPE)
            ->sum('amount');

        $revenueByMonth = $this->revenueByMonth()->values();

        return [
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyRevenueManual' => $monthlyRevenueManual,
            'monthlyRevenueStripe' => $monthlyRevenueStripe,
            'totalClinicsCount' => Clinic::query()->count(),
            'newClinicsThisMonth' => Clinic::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'activeClinicsCount' => PlatformClinicQueries::activeSubscription()->count(),
            'expiredClinicsCount' => PlatformClinicQueries::expiredSubscription()->count(),
            'suspendedClinicsCount' => PlatformClinicQueries::suspended()->count(),
            'expiringSoonCount' => PlatformClinicQueries::expiringWithinDays(
                (int) config('platform.expiring_soon_days', 7)
            )->count(),
            'revenueByMonth' => $revenueByMonth->all(),
            'revenueChartMax' => max(1.0, (float) $revenueByMonth->max(fn (array $row): float => (float) ($row['amount'] ?? 0))),
        ];
    }

    /**
     * Eloquent collections — never cached (avoids incomplete object on unserialize).
     *
     * @return array{
     *     recentSubscriptionPayments: EloquentCollection<int, SubscriptionPayment>,
     *     clinicsExpiringSoon: EloquentCollection<int, Clinic>,
     *     clinicsExpired: EloquentCollection<int, Clinic>
     * }
     */
    private function computeLiveCollections(): array
    {
        $succeeded = fn () => SubscriptionPayment::query()
            ->where('status', SubscriptionPayment::STATUS_SUCCEEDED);

        return [
            'recentSubscriptionPayments' => $succeeded()
                ->with(['clinic:id,name'])
                ->orderByDesc('paid_at')
                ->limit(15)
                ->get(),
            'clinicsExpiringSoon' => PlatformClinicQueries::expiringWithinDays(
                (int) config('platform.expiring_soon_days', 7)
            )
                ->orderBy('subscription_expires_at')
                ->limit(12)
                ->get(['id', 'name', 'subscription_expires_at']),
            'clinicsExpired' => PlatformClinicQueries::expiredSubscription()
                ->orderByDesc('subscription_expires_at')
                ->limit(12)
                ->get(['id', 'name', 'subscription_expires_at', 'subscription_status']),
        ];
    }

    public static function forgetMetricsCache(): void
    {
        foreach (['platform.dashboard.metrics', 'platform.dashboard.metrics.v2', 'platform.dashboard.metrics.v3', 'platform.dashboard.metrics.v4'] as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @return Collection<int, array{label: string, amount: float, manual: float, stripe: float}>
     */
    private function revenueByMonth(): Collection
    {
        return collect(range(5, 0))->map(function (int $monthsAgo): array {
            $d = now()->subMonths($monthsAgo)->startOfMonth();

            $monthInYear = fn () => SubscriptionPayment::query()
                ->where('status', SubscriptionPayment::STATUS_SUCCEEDED)
                ->whereYear('paid_at', $d->year)
                ->whereMonth('paid_at', $d->month);

            $manual = (float) $monthInYear()
                ->where('source', SubscriptionPayment::SOURCE_MANUAL)
                ->sum('amount');
            $stripe = (float) $monthInYear()
                ->where('source', SubscriptionPayment::SOURCE_STRIPE)
                ->sum('amount');

            return [
                'label' => $d->locale(app()->getLocale())->translatedFormat('M Y'),
                'amount' => $manual + $stripe,
                'manual' => $manual,
                'stripe' => $stripe,
            ];
        });
    }
}
