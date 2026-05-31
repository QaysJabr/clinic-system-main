<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;

final class SubscriptionPaymentRecorder
{
    public function recordManual(
        Clinic $clinic,
        float $amount,
        Carbon $paidAt,
        ?string $notes = null
    ): SubscriptionPayment {
        $payment = SubscriptionPayment::query()->create([
            'clinic_id' => $clinic->id,
            'amount' => $amount,
            'paid_at' => $paidAt,
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
            'source' => SubscriptionPayment::SOURCE_MANUAL,
            'notes' => $notes,
        ]);

        PlatformDashboardService::forgetMetricsCache();

        return $payment;
    }

    public function recordStripeSubscriptionPeriod(Clinic $clinic, Subscription $subscription): ?SubscriptionPayment
    {
        if (! $subscription->valid()) {
            return null;
        }

        $stripeId = $subscription->stripe_id ?? (string) $subscription->id;
        $periodEnd = $subscription->currentPeriodEnd() ?? $subscription->ends_at ?? now();
        $reference = 'stripe:sub:'.$stripeId.':period:'.$periodEnd->copy()->utc()->timestamp;

        if (SubscriptionPayment::query()->where('external_reference', $reference)->exists()) {
            return null;
        }

        $subscription->loadMissing('items');
        $stripePrice = $subscription->items->first()?->stripe_price;
        $plan = null;
        $billingCycle = Plan::CYCLE_MONTHLY;

        if (is_string($stripePrice) && $stripePrice !== '') {
            $plan = Plan::query()
                ->where(function ($q) use ($stripePrice): void {
                    $q->where('stripe_price_id', $stripePrice)
                        ->orWhere('stripe_price_yearly_id', $stripePrice);
                })
                ->first();

            if ($plan && $plan->stripe_price_yearly_id === $stripePrice) {
                $billingCycle = Plan::CYCLE_YEARLY;
            }
        }

        if (! $plan && $clinic->plan_id) {
            $plan = Plan::query()->find($clinic->plan_id);
        }

        $amount = $plan ? $plan->amountForBillingCycle($billingCycle) : 0.0;
        if ($amount <= 0) {
            return null;
        }

        $payment = SubscriptionPayment::query()->create([
            'clinic_id' => $clinic->id,
            'amount' => $amount,
            'paid_at' => now(),
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
            'source' => SubscriptionPayment::SOURCE_STRIPE,
            'external_reference' => $reference,
            'notes' => __('platform.stripe_subscription_payment_note', [
                'plan' => $plan->name,
            ]),
        ]);

        PlatformDashboardService::forgetMetricsCache();

        return $payment;
    }
}
