<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Plan;
use Laravel\Cashier\Subscription;

/**
 * Keeps clinics.* subscription fields aligned with Cashier after webhooks / saves.
 */
final class ClinicSubscriptionSyncService
{
    public function syncFromCashierSubscription(Clinic $clinic, Subscription $subscription): void
    {
        $subscription->loadMissing('items');

        $valid = $subscription->valid();

        $expiresAt = $subscription->ends_at ?? $subscription->currentPeriodEnd();
        if ($valid && $expiresAt === null) {
            $expiresAt = now()->addMonth();
        }

        $stripePrice = $subscription->items->first()?->stripe_price ?? $subscription->stripe_price;

        $plan = null;
        if (is_string($stripePrice) && $stripePrice !== '') {
            $plan = Plan::query()
                ->where(function ($q) use ($stripePrice): void {
                    $q->where('stripe_price_id', $stripePrice)
                        ->orWhere('stripe_price_yearly_id', $stripePrice);
                })
                ->first();
        }

        $clinic->forceFill([
            'subscription_status' => $valid ? Clinic::STATUS_ACTIVE : Clinic::STATUS_EXPIRED,
            'subscription_expires_at' => $valid
                ? ($expiresAt ?? $subscription->currentPeriodEnd())
                : ($expiresAt ?? now()->subDay()),
            'subscription_plan' => $plan?->slug ?? $clinic->subscription_plan,
            'plan_id' => $plan?->id ?? $clinic->plan_id,
        ])->save();
    }
}
