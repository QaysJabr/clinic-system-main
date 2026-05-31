<?php

namespace App\Services\Reminders;

use App\Contracts\Reminders\ReminderChannelSender;
use App\Mail\AppointmentReminderMail;
use App\Models\AppointmentReminder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends appointment reminder emails to the clinic owner (staff inbox).
 * Patient records do not include email; the owner receives operational alerts.
 */
final class LaravelMailAppointmentReminderChannelSender implements ReminderChannelSender
{
    public function channel(): string
    {
        return AppointmentReminder::CHANNEL_EMAIL;
    }

    public function send(AppointmentReminder $reminder): void
    {
        $appointment = $reminder->appointment?->loadMissing('clinic.owner', 'patient', 'doctor');
        if (! $appointment) {
            return;
        }

        $email = $appointment->clinic?->owner?->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('appointment.reminder.email_skipped', [
                'reminder_id' => $reminder->id,
                'appointment_id' => $appointment->id,
                'reason' => 'no_clinic_owner_email',
            ]);

            return;
        }

        Mail::to($email)->send(new AppointmentReminderMail($reminder));
    }
}
