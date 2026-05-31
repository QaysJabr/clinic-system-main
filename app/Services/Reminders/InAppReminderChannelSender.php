<?php

namespace App\Services\Reminders;

use App\Contracts\Reminders\ReminderChannelSender;
use App\Models\AppointmentReminder;
use App\Services\InAppNotificationService;

final class InAppReminderChannelSender implements ReminderChannelSender
{
    public function __construct(
        private readonly InAppNotificationService $notifications,
    ) {}

    public function channel(): string
    {
        return AppointmentReminder::CHANNEL_IN_APP;
    }

    public function send(AppointmentReminder $reminder): void
    {
        $appointment = $reminder->appointment;
        if ($appointment) {
            $this->notifications->pushForAppointmentIfRelevant($appointment);
        }
    }
}
