<?php

namespace App\Observers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\InAppNotificationService;
use App\Services\Scheduling\AppointmentReminderPlanner;
use App\Support\ClinicCacheInvalidator;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AppointmentObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService,
        private readonly AppointmentReminderPlanner $reminderPlanner,
    ) {}

    public function created(Appointment $appointment): void
    {
        $this->reminderPlanner->syncForAppointment($appointment);
        ClinicCacheInvalidator::flush($appointment->clinic_id);
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged(['appointment_date', 'start_time', 'end_time', 'status'])) {
            $this->reminderPlanner->syncForAppointment($appointment);
        }

        if ($appointment->wasChanged('status') && $appointment->status === AppointmentStatus::Cancelled->value) {
            $this->inAppNotificationService->notifyAppointmentCancelled($appointment);
        }

        ClinicCacheInvalidator::flush($appointment->clinic_id);
    }
}
