<?php

namespace App\Services\Platform;

use App\Mail\ManualSubscriptionPaymentMail;
use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Mail;

final class PlatformManualPaymentNotifier
{
    public function notifyOwner(Clinic $clinic, SubscriptionPayment $payment): bool
    {
        if (! config('platform.notify_owner_manual_payment', true)) {
            return false;
        }

        $owner = $clinic->owner;
        if (! $owner || ! $owner->email) {
            return false;
        }

        try {
            Mail::to($owner->email)->send(new ManualSubscriptionPaymentMail($clinic, $payment));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }
}
