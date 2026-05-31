<?php

namespace App\Observers;

use App\Models\Visit;
use App\Services\InAppNotificationService;
use App\Services\Inventory\InventoryProcedureConsumptionService;
use App\Services\Scheduling\AppointmentLifecycleService;
use App\Support\ClinicCacheInvalidator;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class VisitObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService,
        private readonly AppointmentLifecycleService $appointmentLifecycle,
        private readonly InventoryProcedureConsumptionService $inventoryConsumption,
    ) {}

    public function created(Visit $visit): void
    {
        $this->inAppNotificationService->notifyVisitRecorded($visit);
        ClinicCacheInvalidator::flush($visit->clinic_id);
    }

    public function updated(Visit $visit): void
    {
        if ($visit->wasChanged('status')) {
            $this->appointmentLifecycle->syncFromVisit($visit);

            if ($visit->status === Visit::STATUS_COMPLETED) {
                $this->inventoryConsumption->consumeForCompletedVisit($visit);
            }
        }

        ClinicCacheInvalidator::flush($visit->clinic_id);
    }
}
