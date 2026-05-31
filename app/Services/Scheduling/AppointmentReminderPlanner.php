<?php

namespace App\Services\Scheduling;

use App\Enums\AppointmentStatus;
use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Support\Scheduling\AppointmentTimeRange;
use App\Support\Scheduling\SchedulingSettings;

/**
 * Queues reminder rows and dispatches jobs (email/SMS/WhatsApp-ready).
 */
final class AppointmentReminderPlanner
{
    public function syncForAppointment(Appointment $appointment): void
    {
        $settings = SchedulingSettings::fromClinicSettings();

        if (! $settings->remindersEnabled) {
            return;
        }

        if (! in_array($appointment->status, AppointmentStatus::blocking(), true)) {
            $this->cancelPending($appointment);

            return;
        }

        try {
            $startsAt = AppointmentTimeRange::fromAppointment($appointment)->start;
        } catch (\InvalidArgumentException) {
            return;
        }

        AppointmentReminder::query()
            ->where('appointment_id', $appointment->id)
            ->where('status', AppointmentReminder::STATUS_PENDING)
            ->delete();

        foreach (config('scheduling.reminder_lead_hours', [24, 2]) as $hours) {
            $scheduledFor = $startsAt->copy()->subHours((int) $hours);
            if ($scheduledFor->isPast()) {
                continue;
            }

            $channels = [AppointmentReminder::CHANNEL_EMAIL, AppointmentReminder::CHANNEL_IN_APP];
            if (config('reminders.sms.enabled', false)) {
                $channels[] = AppointmentReminder::CHANNEL_SMS;
            }

            foreach ($channels as $channel) {
                $reminder = AppointmentReminder::query()->create([
                    'appointment_id' => $appointment->id,
                    'channel' => $channel,
                    'scheduled_for' => $scheduledFor,
                    'status' => AppointmentReminder::STATUS_PENDING,
                    'payload' => ['lead_hours' => $hours],
                ]);

                SendAppointmentReminderJob::dispatch($reminder->id)
                    ->delay($scheduledFor);
            }
        }
    }

    public function cancelPending(Appointment $appointment): void
    {
        AppointmentReminder::query()
            ->where('appointment_id', $appointment->id)
            ->where('status', AppointmentReminder::STATUS_PENDING)
            ->update(['status' => AppointmentReminder::STATUS_CANCELLED]);
    }
}
