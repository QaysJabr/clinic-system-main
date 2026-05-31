<?php

namespace App\Observers;

use App\Models\AppNotification;
use App\Services\Push\PushNotificationDispatcher;

final class AppNotificationObserver
{
    public function created(AppNotification $notification): void
    {
        if ($notification->user_id === null) {
            return;
        }

        app(PushNotificationDispatcher::class)->dispatchForUser(
            (int) $notification->user_id,
            (string) $notification->title,
            (string) $notification->message,
            $this->payload($notification),
        );
    }

    /**
     * @return array<string, string>
     */
    private function payload(AppNotification $notification): array
    {
        return array_filter([
            'notification_id' => (string) $notification->id,
            'type' => (string) $notification->type,
            'related_type' => $notification->related_type ? class_basename($notification->related_type) : '',
            'related_id' => $notification->related_id !== null ? (string) $notification->related_id : '',
        ]);
    }
}
