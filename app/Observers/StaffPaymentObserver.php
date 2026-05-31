<?php

namespace App\Observers;

use App\Models\StaffPayment;
use App\Services\InAppNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class StaffPaymentObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService
    ) {}

    public function saved(StaffPayment $staffPayment): void
    {
        if ((float) $staffPayment->remaining_amount > 0.00001) {
            $this->inAppNotificationService->notifyPayrollOutstanding($staffPayment);
        }

        if (! $staffPayment->wasRecentlyCreated) {
            $originalRemaining = $staffPayment->getOriginal('remaining_amount');
            if (
                $originalRemaining !== null
                && (float) $originalRemaining > 0.00001
                && (float) $staffPayment->remaining_amount <= 0.00001
            ) {
                $this->inAppNotificationService->notifyPayrollSettled($staffPayment);
            }
        }
    }
}
