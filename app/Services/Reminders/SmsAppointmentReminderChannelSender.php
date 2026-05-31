<?php

namespace App\Services\Reminders;

use App\Contracts\Reminders\ReminderChannelSender;
use App\Models\AppointmentReminder;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class SmsAppointmentReminderChannelSender implements ReminderChannelSender
{
    public function channel(): string
    {
        return AppointmentReminder::CHANNEL_SMS;
    }

    public function send(AppointmentReminder $reminder): void
    {
        if (! config('reminders.sms.enabled', false)) {
            return;
        }

        $appointment = $reminder->appointment?->loadMissing('patient', 'doctor', 'clinic');
        if (! $appointment) {
            return;
        }

        $patient = $appointment->patient;
        $to = PhoneNumber::toE164(
            PhoneNumber::normalizeForSms($patient?->phone)
        );

        if ($to === null) {
            Log::info('appointment.reminder.sms_skipped', [
                'reminder_id' => $reminder->id,
                'appointment_id' => $appointment->id,
                'reason' => 'no_patient_phone',
            ]);

            return;
        }

        $lead = (int) ($reminder->payload['lead_hours'] ?? 0);
        $clinicName = $appointment->clinic?->name ?? config('app.name');
        $body = __('reminders.sms_body', [
            'clinic' => $clinicName,
            'hours' => $lead,
            'date' => $appointment->appointment_date?->format('Y-m-d') ?? '',
            'time' => substr((string) ($appointment->start_time ?? ''), 0, 5),
            'doctor' => $appointment->doctor?->full_name ?? '',
        ]);

        $driver = (string) config('reminders.sms.driver', 'log');

        if ($driver === 'twilio') {
            $this->sendViaTwilio($to, $body, $reminder->id);

            return;
        }

        Log::info('appointment.reminder.sms', [
            'reminder_id' => $reminder->id,
            'appointment_id' => $appointment->id,
            'to' => $to,
            'body' => $body,
        ]);
    }

    private function sendViaTwilio(string $to, string $body, int $reminderId): void
    {
        $sid = (string) config('reminders.sms.twilio.account_sid');
        $token = (string) config('reminders.sms.twilio.auth_token');
        $from = (string) config('reminders.sms.twilio.from');

        if ($sid === '' || $token === '' || $from === '') {
            throw new RuntimeException('Twilio SMS is not configured (TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM_NUMBER).');
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => $from,
                'Body' => $body,
            ]);

        if (! $response->successful()) {
            Log::error('appointment.reminder.sms_twilio_failed', [
                'reminder_id' => $reminderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Twilio SMS request failed with HTTP '.$response->status());
        }
    }
}
