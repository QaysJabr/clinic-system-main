<?php

namespace App\Services\Saas;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Clinic;
use App\Models\SubscriptionReminderDispatch;
use App\Services\InAppNotificationService;
use App\Services\SubscriptionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

final class SubscriptionExpiryReminderService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly InAppNotificationService $notifications,
    ) {}

    /**
     * @return array{email: int, in_app: int, skipped: int}
     */
    public function dispatchDueReminders(?Carbon $asOf = null): array
    {
        if (! config('saas.subscription_reminders.enabled', true)) {
            return ['email' => 0, 'in_app' => 0, 'skipped' => 0];
        }

        $asOf ??= now();
        $daysList = config('saas.subscription_reminders.days_before', [7, 3]);
        $daysList = array_values(array_unique(array_filter(array_map('intval', (array) $daysList), fn (int $d) => $d > 0)));

        $stats = ['email' => 0, 'in_app' => 0, 'skipped' => 0];

        if ($daysList === []) {
            return $stats;
        }

        Clinic::query()
            ->where('is_active', true)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '>', $asOf)
            ->with('owner:id,email,email_verified_at,name')
            ->chunkById(50, function ($clinics) use ($asOf, $daysList, &$stats): void {
                foreach ($clinics as $clinic) {
                    if ($this->subscriptions->isActive($clinic) === false) {
                        $stats['skipped']++;

                        continue;
                    }

                    $daysRemaining = (int) ceil($asOf->diffInDays($clinic->subscription_expires_at, false));
                    if (! in_array($daysRemaining, $daysList, true)) {
                        continue;
                    }

                    if (config('saas.subscription_reminders.email', true)) {
                        $stats['email'] += $this->sendEmailIfNeeded($clinic, $daysRemaining) ? 1 : 0;
                    }

                    if (config('saas.subscription_reminders.in_app', true)) {
                        $stats['in_app'] += $this->sendInAppIfNeeded($clinic, $daysRemaining) ? 1 : 0;
                    }
                }
            });

        return $stats;
    }

    private function sendEmailIfNeeded(Clinic $clinic, int $daysRemaining): bool
    {
        if ($this->wasDispatched($clinic->id, $daysRemaining, SubscriptionReminderDispatch::CHANNEL_EMAIL)) {
            return false;
        }

        $owner = $clinic->owner;
        if (! $owner || ! $owner->email || ! $owner->email_verified_at) {
            return false;
        }

        try {
            Mail::to($owner->email)->send(new SubscriptionExpiringMail($clinic, $daysRemaining));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        $this->recordDispatch($clinic->id, $daysRemaining, SubscriptionReminderDispatch::CHANNEL_EMAIL);

        return true;
    }

    private function sendInAppIfNeeded(Clinic $clinic, int $daysRemaining): bool
    {
        if ($this->wasDispatched($clinic->id, $daysRemaining, SubscriptionReminderDispatch::CHANNEL_IN_APP)) {
            return false;
        }

        $this->notifications->notifySubscriptionExpiring($clinic, $daysRemaining);
        $this->recordDispatch($clinic->id, $daysRemaining, SubscriptionReminderDispatch::CHANNEL_IN_APP);

        return true;
    }

    private function wasDispatched(int $clinicId, int $daysBefore, string $channel): bool
    {
        return SubscriptionReminderDispatch::query()
            ->where('clinic_id', $clinicId)
            ->where('days_before', $daysBefore)
            ->where('channel', $channel)
            ->exists();
    }

    private function recordDispatch(int $clinicId, int $daysBefore, string $channel): void
    {
        SubscriptionReminderDispatch::query()->create([
            'clinic_id' => $clinicId,
            'days_before' => $daysBefore,
            'channel' => $channel,
            'sent_at' => now(),
        ]);
    }
}
