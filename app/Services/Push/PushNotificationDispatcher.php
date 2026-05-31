<?php

namespace App\Services\Push;

use App\Jobs\SendPushToUserJob;
use App\Models\DevicePushToken;
use Illuminate\Support\Collection;

final class PushNotificationDispatcher
{
    /**
     * @param  Collection<int, int>|array<int, int>  $userIds
     * @param  array<string, string>  $data
     */
    public function dispatchToUsers(Collection|array $userIds, string $title, string $body, array $data = []): void
    {
        if (! config('push.enabled', false)) {
            return;
        }

        $ids = $userIds instanceof Collection ? $userIds : collect($userIds);
        $ids = $ids->unique()->filter(fn ($id) => (int) $id > 0)->values();

        if ($ids->isEmpty()) {
            return;
        }

        $hasAnyToken = DevicePushToken::query()
            ->whereIn('user_id', $ids->all())
            ->exists();

        if (! $hasAnyToken) {
            return;
        }

        $queue = (string) config('push.queue', 'default');

        foreach ($ids as $userId) {
            SendPushToUserJob::dispatch((int) $userId, $title, $body, $data)->onQueue($queue);
        }
    }

    public function dispatchForUser(int $userId, string $title, string $body, array $data = []): void
    {
        $this->dispatchToUsers(collect([$userId]), $title, $body, $data);
    }
}
