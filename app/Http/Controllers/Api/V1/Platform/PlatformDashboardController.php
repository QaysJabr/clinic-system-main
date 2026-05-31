<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\PlatformClinicResource;
use App\Http\Resources\Api\PlatformPlanResource;
use App\Http\Resources\Api\SubscriptionPaymentResource;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Services\InAppNotificationService;
use App\Services\Platform\PlatformManualPaymentNotifier;
use App\Services\PlatformDashboardService;
use App\Services\SubscriptionPaymentRecorder;
use App\Support\PlatformClinicIndexQuery;
use App\Support\PlatformSubscriptionPaymentQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class PlatformDashboardController extends ApiController
{
    public function __invoke(PlatformDashboardService $dashboard): JsonResponse
    {
        $metrics = $dashboard->metrics();

        return $this->ok([
            'total_revenue' => (float) ($metrics['totalRevenue'] ?? 0),
            'monthly_revenue' => (float) ($metrics['monthlyRevenue'] ?? 0),
            'monthly_revenue_manual' => (float) ($metrics['monthlyRevenueManual'] ?? 0),
            'monthly_revenue_stripe' => (float) ($metrics['monthlyRevenueStripe'] ?? 0),
            'total_clinics' => (int) ($metrics['totalClinicsCount'] ?? 0),
            'new_clinics_this_month' => (int) ($metrics['newClinicsThisMonth'] ?? 0),
            'active_clinics' => (int) ($metrics['activeClinicsCount'] ?? 0),
            'expired_clinics' => (int) ($metrics['expiredClinicsCount'] ?? 0),
            'suspended_clinics' => (int) ($metrics['suspendedClinicsCount'] ?? 0),
            'expiring_soon_count' => (int) ($metrics['expiringSoonCount'] ?? 0),
            'expiring_soon_days' => (int) config('platform.expiring_soon_days', 7),
            'revenue_by_month' => collect($metrics['revenueByMonth'] ?? [])->map(fn (array $row): array => [
                'label' => (string) ($row['label'] ?? ''),
                'amount' => (float) ($row['amount'] ?? 0),
                'manual' => (float) ($row['manual'] ?? 0),
                'stripe' => (float) ($row['stripe'] ?? 0),
            ])->values()->all(),
            'recent_payments' => SubscriptionPaymentResource::collection(
                $metrics['recentSubscriptionPayments'] ?? collect()
            ),
            'clinics_expiring_soon' => PlatformClinicResource::collection(
                $metrics['clinicsExpiringSoon'] ?? collect()
            ),
            'clinics_expired' => PlatformClinicResource::collection(
                $metrics['clinicsExpired'] ?? collect()
            ),
        ]);
    }
}
