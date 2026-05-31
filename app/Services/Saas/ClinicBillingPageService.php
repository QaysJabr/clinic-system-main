<?php

namespace App\Services\Saas;

use App\Models\Clinic;
use App\Models\ClinicSubscription;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\StripeWebhookEvent;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class ClinicBillingPageService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly ClinicStripeInvoiceService $stripeInvoices,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?Clinic $clinic): array
    {
        if (! $clinic) {
            return [
                'daysRemaining' => null,
                'isActive' => false,
                'alertTier' => 'none',
                'usage' => null,
                'payments' => collect(),
                'stripeInvoices' => [],
                'trial' => null,
                'stripeWebhookEvents' => collect(),
            ];
        }

        $clinic->loadMissing(['plan', 'latestClinicSubscription.plan', 'owner:id,name,email']);

        $daysRemaining = null;
        if ($clinic->subscription_expires_at) {
            $daysRemaining = (int) ceil(now()->diffInDays($clinic->subscription_expires_at, false));
        }

        $isActive = $this->subscriptions->isActive($clinic);
        $alertTier = $this->resolveAlertTier($clinic, $daysRemaining, $isActive);

        return [
            'daysRemaining' => $daysRemaining,
            'isActive' => $isActive,
            'alertTier' => $alertTier,
            'usage' => $this->usageSnapshot($clinic),
            'payments' => $this->paymentHistory($clinic),
            'stripeInvoices' => $this->stripeInvoices->recentForClinic($clinic),
            'billingCycleLabel' => $this->billingCycleLabel($clinic->latestClinicSubscription),
            'subscriptionAmount' => $this->formatAmount($clinic->latestClinicSubscription, $clinic->plan),
            'subscriptionStartsAt' => $clinic->latestClinicSubscription?->starts_at,
            'trial' => $this->trialSnapshot($clinic),
            'stripeWebhookEvents' => $this->recentWebhookEvents($clinic),
            'currentPlanId' => $clinic->plan_id,
        ];
    }

    /**
     * @return array{active: bool, days_remaining: ?int, ends_at: ?Carbon, label: ?string}|null
     */
    private function trialSnapshot(Clinic $clinic): ?array
    {
        $sub = $clinic->latestClinicSubscription;
        $trialEnds = $clinic->trial_ends_at ?? $sub?->ends_at;

        $onTrial = $sub?->status === ClinicSubscription::STATUS_TRIAL
            || ($clinic->trial_ends_at && $clinic->trial_ends_at->isFuture());

        if (! $onTrial || ! $trialEnds || $trialEnds->isPast()) {
            return null;
        }

        $days = (int) ceil(now()->diffInDays($trialEnds, false));

        return [
            'active' => true,
            'days_remaining' => max(0, $days),
            'ends_at' => $trialEnds,
            'label' => __('saas.trial_banner', ['days' => max(0, $days), 'date' => $trialEnds->format('d/m/Y')]),
        ];
    }

    /**
     * @return Collection<int, StripeWebhookEvent>
     */
    private function recentWebhookEvents(Clinic $clinic): Collection
    {
        if (! config('saas.webhook_log.enabled', true)) {
            return collect();
        }

        $limit = max(1, (int) config('saas.webhook_log.display_limit', 8));

        return StripeWebhookEvent::query()
            ->where('clinic_id', $clinic->id)
            ->orderByDesc('received_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{patients: int, users: int, max_patients: ?int, max_users: ?int, patients_unlimited: bool, users_unlimited: bool}
     */
    private function usageSnapshot(Clinic $clinic): array
    {
        $plan = $clinic->plan;

        return [
            'patients' => Patient::query()->where('clinic_id', $clinic->id)->count(),
            'users' => User::query()->where('clinic_id', $clinic->id)->count(),
            'max_patients' => $plan?->max_patients,
            'max_users' => $plan?->max_users,
            'patients_unlimited' => $plan === null || $plan->max_patients === null,
            'users_unlimited' => $plan === null || $plan->max_users === null,
        ];
    }

    /**
     * @return Collection<int, SubscriptionPayment>
     */
    private function paymentHistory(Clinic $clinic): Collection
    {
        return SubscriptionPayment::query()
            ->where('clinic_id', $clinic->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get();
    }

    private function resolveAlertTier(Clinic $clinic, ?int $daysRemaining, bool $isActive): string
    {
        if ($clinic->subscription_status === Clinic::STATUS_EXPIRED) {
            return 'expired';
        }

        if ($clinic->subscription_expires_at && $clinic->subscription_expires_at->isPast()) {
            return 'expired';
        }

        if ($daysRemaining !== null && $daysRemaining < 0) {
            return 'expired';
        }

        if (! $isActive) {
            return 'expired';
        }

        if ($daysRemaining !== null && $daysRemaining <= 7) {
            return 'warning';
        }

        return 'active';
    }

    private function billingCycleLabel(?ClinicSubscription $subscription): ?string
    {
        if (! $subscription) {
            return null;
        }

        return match ($subscription->billing_cycle) {
            Plan::CYCLE_YEARLY => __('saas.cycle_yearly'),
            Plan::CYCLE_MONTHLY => __('saas.cycle_monthly'),
            default => $subscription->billing_cycle,
        };
    }

    private function formatAmount(?ClinicSubscription $subscription, ?Plan $plan): ?string
    {
        if ($subscription?->amount !== null) {
            return number_format((float) $subscription->amount, 2);
        }

        if ($plan && $subscription) {
            $amount = $plan->amountForBillingCycle($subscription->billing_cycle);

            return number_format($amount, 2);
        }

        return null;
    }

    public static function paymentSourceLabel(string $source): string
    {
        return match ($source) {
            SubscriptionPayment::SOURCE_STRIPE => __('saas.payment_source_stripe'),
            SubscriptionPayment::SOURCE_MANUAL => __('saas.payment_source_manual'),
            default => $source,
        };
    }

    public static function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            SubscriptionPayment::STATUS_SUCCEEDED => __('saas.payment_status_succeeded'),
            SubscriptionPayment::STATUS_PENDING => __('saas.payment_status_pending'),
            SubscriptionPayment::STATUS_FAILED => __('saas.payment_status_failed'),
            default => $status,
        };
    }

    public static function stripeSubscriptionStatusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return __('common.em_dash');
        }

        $key = 'saas.stripe_sub_status_'.$status;
        $label = __($key);

        return $label !== $key ? $label : $status;
    }
}
