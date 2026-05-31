<?php

namespace App\Services\Saas;

use App\Models\Clinic;
use App\Models\ClinicSubscription;
use App\Models\Plan;
use App\Services\ClinicSubscriptionSyncService;
use App\Services\SubscriptionService;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;

final class ClinicCheckoutService
{
    public function __construct(
        private readonly ClinicSubscriptionSyncService $syncService,
        private readonly SubscriptionService $subscriptions,
    ) {}

    /**
     * @return array{mode: string, url?: string, message: string, billing?: array<string, mixed>}
     */
    public function checkout(Clinic $clinic, Plan $plan, string $billingCycle, ?string $promotionCode = null): array
    {
        $priceId = $plan->stripePriceIdForBillingCycle($billingCycle);
        $stripeReady = ! empty(config('cashier.secret')) && is_string($priceId) && $priceId !== '';

        if ($stripeReady) {
            $cashierSub = $clinic->subscription('default');

            if ($cashierSub && $cashierSub->valid() && ! $cashierSub->ended()) {
                return $this->swapExistingSubscription($clinic, $cashierSub, $plan, $billingCycle, $priceId);
            }

            return $this->startStripeCheckout($clinic, $priceId, $promotionCode);
        }

        return $this->activateManualSubscription($clinic, $plan, $billingCycle);
    }

    /**
     * @return array{mode: string, message: string, billing: array<string, mixed>}
     */
    private function swapExistingSubscription(
        Clinic $clinic,
        Subscription $cashierSub,
        Plan $plan,
        string $billingCycle,
        string $priceId,
    ): array {
        $currentPrice = $cashierSub->items->first()?->stripe_price;

        if ($currentPrice === $priceId) {
            throw ValidationException::withMessages([
                'plan' => [__('saas.checkout_same_plan')],
            ]);
        }

        $cashierSub->swap($priceId, [
            'proration_behavior' => 'create_prorations',
        ]);

        $cashierSub = $cashierSub->fresh() ?? $cashierSub;
        $this->syncService->syncFromCashierSubscription($clinic, $cashierSub);
        $this->subscriptions->updatePlanOnRecord($clinic, $plan, $billingCycle);

        $clinic->refresh()->load(['plan', 'latestClinicSubscription.plan']);

        return [
            'mode' => 'swapped',
            'message' => __('saas.subscription_plan_changed'),
            'billing' => $this->billingSnapshot($clinic),
        ];
    }

    /**
     * @return array{mode: string, url: string, message: string}
     */
    private function startStripeCheckout(Clinic $clinic, string $priceId, ?string $promotionCode): array
    {
        $builder = $clinic->newSubscription('default', $priceId);

        if (config('saas.checkout.allow_promotion_codes', true)) {
            $builder->allowPromotionCodes();
        }

        $promotionCode = is_string($promotionCode) ? trim($promotionCode) : '';
        if ($promotionCode !== '') {
            $builder->withPromotionCode($promotionCode);
        }

        /** @var Checkout $checkout */
        $checkout = $builder->checkout([
            'success_url' => route('saas.billing').'?checkout=success',
            'cancel_url' => route('saas.pricing'),
        ]);

        return [
            'mode' => 'stripe',
            'url' => $checkout->url,
            'message' => __('saas.redirecting_stripe'),
        ];
    }

    /**
     * @return array{mode: string, message: string, billing: array<string, mixed>}
     */
    private function activateManualSubscription(Clinic $clinic, Plan $plan, string $billingCycle): array
    {
        $manualAllowed = app()->environment('local')
            || filter_var(env('SAAS_ALLOW_MANUAL_SUBSCRIBE', false), FILTER_VALIDATE_BOOL);

        if (! $manualAllowed) {
            throw ValidationException::withMessages([
                'billing_cycle' => [__('saas.stripe_price_missing')],
            ]);
        }

        $sub = $this->subscriptions->subscribe($clinic, $plan, $billingCycle);
        $clinic->refresh()->load(['plan', 'latestClinicSubscription.plan']);

        return [
            'mode' => 'manual',
            'message' => __('saas.subscription_activated'),
            'billing' => $this->billingSnapshot($clinic, $sub),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function billingSnapshot(Clinic $clinic, ?ClinicSubscription $row = null): array
    {
        $row ??= $clinic->latestClinicSubscription;
        $expires = $clinic->subscription_expires_at;
        $daysRemaining = $expires ? (int) ceil(now()->diffInDays($expires, false)) : null;

        return [
            'plan_name' => $clinic->plan?->name,
            'subscription_status' => $clinic->subscription_status,
            'expires_at' => $expires?->format('d/m/Y H:i'),
            'days_remaining' => $daysRemaining,
            'clinic_subscription_status' => $row?->status,
            'billing_cycle' => $row?->billing_cycle,
        ];
    }
}
