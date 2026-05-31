<?php

namespace App\Jobs;

use App\Models\AppointmentReminder;
use App\Services\Reminders\ReminderChannelRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches appointment reminders when due (email/SMS hooks ready).
 */
class SendAppointmentReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $reminderId,
    ) {
        $this->onQueue(config('performance.queues.reminders', 'default'));
    }

    public function handle(ReminderChannelRegistry $channels): void
    {
        $reminder = AppointmentReminder::query()->with('appointment.patient', 'appointment.doctor')->find($this->reminderId);

        if (! $reminder || $reminder->status !== AppointmentReminder::STATUS_PENDING) {
            return;
        }

        if ($reminder->scheduled_for->isFuture()) {
            return;
        }

        if (! $reminder->appointment) {
            $reminder->update(['status' => AppointmentReminder::STATUS_CANCELLED]);

            return;
        }

        try {
            $channels->send($reminder);
            $reminder->update([
                'status' => AppointmentReminder::STATUS_SENT,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('appointment.reminder.failed', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage(),
            ]);
            $reminder->update(['status' => AppointmentReminder::STATUS_FAILED]);
            throw $e;
        }
    }
}
