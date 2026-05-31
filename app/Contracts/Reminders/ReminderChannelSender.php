<?php

namespace App\Contracts\Reminders;

use App\Models\AppointmentReminder;

interface ReminderChannelSender
{
    public function channel(): string;

    public function send(AppointmentReminder $reminder): void;
}
