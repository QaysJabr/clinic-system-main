<?php

namespace App\Support;

use App\Models\Clinic;
use Laravel\Cashier\Subscription;

final class PlatformStripePresentation
{
    public static function statusLabel(Clinic $clinic): string
    {
        return self::statusLabelForSubscription($clinic->subscription('default'));
    }

    public static function statusLabelForSubscription(?Subscription $subscription): string
    {
        if ($subscription === null) {
            return __('platform.stripe_not_connected');
        }

        if ($subscription->onGracePeriod()) {
            return __('platform.stripe_grace_period');
        }

        if ($subscription->ended()) {
            return __('platform.stripe_canceled');
        }

        if ($subscription->valid()) {
            return __('platform.stripe_active');
        }

        return __('platform.stripe_unknown');
    }
}
