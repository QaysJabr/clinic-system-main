<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\InAppNotificationService;
use App\Services\PatientFileNumberService;
use App\Support\ClinicCacheInvalidator;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PatientObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService
    ) {}

    public function creating(Patient $patient): void
    {
        if (filled($patient->file_number)) {
            return;
        }

        $clinicId = (int) ($patient->clinic_id ?? 0);
        $patient->file_number = app(PatientFileNumberService::class)->generate(
            $clinicId > 0 ? $clinicId : null
        );
    }

    public function created(Patient $patient): void
    {
        $this->inAppNotificationService->notifyPatientRegistered($patient);
        ClinicCacheInvalidator::flush($patient->clinic_id);
    }
}
