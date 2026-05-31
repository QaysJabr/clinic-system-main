<?php

namespace App\Listeners;

use App\Models\Clinic;
use App\Models\StripeWebhookEvent;
use Laravel\Cashier\Events\WebhookHandled;

final class RecordStripeWebhookEvent
{
    public function handle(WebhookHandled $event): void
    {
        if (! config('saas.webhook_log.enabled', true)) {
            return;
        }

        $payload = $event->payload;
        $eventId = (string) ($payload['id'] ?? '');
        $eventType = (string) ($payload['type'] ?? 'unknown');

        if ($eventId === '') {
            return;
        }

        if (StripeWebhookEvent::query()->where('stripe_event_id', $eventId)->exists()) {
            return;
        }

        $object = $payload['data']['object'] ?? [];
        $customerId = isset($object['customer']) ? (string) $object['customer'] : null;

        $clinicId = null;
        if ($customerId) {
            $clinicId = Clinic::query()->where('stripe_id', $customerId)->value('id');
        }

        $summary = $this->buildSummary($eventType, $object);

        StripeWebhookEvent::query()->create([
            'stripe_event_id' => $eventId,
            'event_type' => $eventType,
            'clinic_id' => $clinicId,
            'stripe_customer_id' => $customerId,
            'summary' => $summary,
            'received_at' => now(),
        ]);

        $keep = max(5, (int) config('saas.webhook_log.keep_per_clinic', 30));
        if ($clinicId) {
            $staleIds = StripeWebhookEvent::query()
                ->where('clinic_id', $clinicId)
                ->orderByDesc('received_at')
                ->skip($keep)
                ->take(100)
                ->pluck('id');

            if ($staleIds->isNotEmpty()) {
                StripeWebhookEvent::query()->whereIn('id', $staleIds)->delete();
            }
        }
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function buildSummary(string $eventType, array $object): string
    {
        $parts = [$eventType];

        if (isset($object['status'])) {
            $parts[] = 'status='.$object['status'];
        }

        if (isset($object['id']) && is_string($object['id'])) {
            $parts[] = 'id='.$object['id'];
        }

        return mb_substr(implode(' · ', $parts), 0, 250);
    }
}
