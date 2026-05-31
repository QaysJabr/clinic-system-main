<?php

namespace App\Services\Reminders;

use App\Contracts\Reminders\ReminderChannelSender;
use App\Models\AppointmentReminder;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder for email/SMS/WhatsApp providers — logs until a provider is wired.
 */
final class LogEmailReminderChannelSender implements ReminderChannelSender
{
    public function channel(): string
    {
        return AppointmentReminder::CHANNEL_EMAIL;
    }

    public function send(AppointmentReminder $reminder): void
    {
        Log::info('appointment.reminder.email', [
            'appointment_id' => $reminder->appointment_id,
            'reminder_id' => $reminder->id,
            'channel' => $reminder->channel,
        ]);
    }
}
