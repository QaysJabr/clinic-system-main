<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\InAppNotificationService;
use App\Support\ClinicCacheInvalidator;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PatientObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService
    ) {}

    public function created(Patient $patient): void
    {
        $this->inAppNotificationService->notifyPatientRegistered($patient);
        ClinicCacheInvalidator::flush($patient->clinic_id);
    }
}
