<?php

namespace App\Observers;

use App\Models\DoctorEarning;
use App\Services\InAppNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class DoctorEarningObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService
    ) {}

    public function created(DoctorEarning $doctorEarning): void
    {
        $this->inAppNotificationService->notifyDoctorEarningCreated($doctorEarning);
    }

    public function updated(DoctorEarning $doctorEarning): void
    {
        if ($doctorEarning->wasChanged('status') && $doctorEarning->status === DoctorEarning::STATUS_PAID) {
            $this->inAppNotificationService->notifyDoctorEarningPaid($doctorEarning);
        }
    }
}
