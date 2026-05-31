<?php

namespace App\Jobs;

use App\Models\DevicePushToken;
use App\Services\Push\DevicePushTokenService;
use App\Services\Push\FcmPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendPushToUserJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public array $data = [],
    ) {
        $this->onQueue((string) config('push.queue', 'default'));
    }

    public function handle(FcmPushService $fcm, DevicePushTokenService $tokens): void
    {
        if (! config('push.enabled', false)) {
            return;
        }

        $deviceTokens = DevicePushToken::query()
            ->where('user_id', $this->userId)
            ->pluck('token');

        foreach ($deviceTokens as $deviceToken) {
            $result = $fcm->sendToToken($deviceToken, $this->title, $this->body, $this->data);
            if ($result === FcmPushService::RESULT_INVALID_TOKEN) {
                $tokens->deleteToken($deviceToken);
            }
        }
    }
}
