<?php

namespace App\Observers;

use App\Models\Clinic;
use App\Services\ClinicSubscriptionSyncService;
use App\Services\InAppNotificationService;
use App\Services\SubscriptionPaymentRecorder;
use App\Support\AuditLogger;
use Laravel\Cashier\Subscription;

final class CashierSubscriptionObserver
{
    public function __construct(
        private readonly ClinicSubscriptionSyncService $syncService,
        private readonly InAppNotificationService $inAppNotificationService,
        private readonly SubscriptionPaymentRecorder $paymentRecorder
    ) {}

    public function saved(Subscription $subscription): void
    {
        $owner = $subscription->owner;
        if (! $owner instanceof Clinic) {
            return;
        }

        $this->syncService->syncFromCashierSubscription($owner, $subscription);
        AuditLogger::security(
            'subscription_sync',
            'billing',
            $subscription->id,
            'Stripe subscription updated for clinic #'.$owner->id,
            null,
            ['stripe_status' => $subscription->stripe_status, 'clinic_id' => $owner->id],
        );
        $this->paymentRecorder->recordStripeSubscriptionPeriod($owner, $subscription);
        $this->inAppNotificationService->notifyPlatformClinicSubscription($owner, $subscription);
    }
}
