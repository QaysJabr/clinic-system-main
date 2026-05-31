<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FcmPushService
{
    public function __construct(
        private readonly FcmAccessTokenProvider $tokens,
    ) {}

    public const RESULT_SENT = 'sent';

    public const RESULT_SKIPPED = 'skipped';

    public const RESULT_FAILED = 'failed';

    public const RESULT_INVALID_TOKEN = 'invalid_token';

    /**
     * @param  array<string, string>  $data
     */
    public function sendToToken(string $deviceToken, string $title, string $body, array $data = []): string
    {
        if (! config('push.enabled', false)) {
            return self::RESULT_SKIPPED;
        }

        $projectId = (string) config('push.firebase.project_id');
        if ($projectId === '') {
            $projectId = $this->projectIdFromCredentials();
        }

        if ($projectId === '') {
            Log::warning('FCM push skipped: FIREBASE_PROJECT_ID not set.');

            return self::RESULT_SKIPPED;
        }

        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $this->stringifyData($data),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'clinic_default',
                ],
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        try {
            $accessToken = $this->tokens->get();
        } catch (\Throwable $e) {
            Log::error('FCM OAuth error', ['message' => $e->getMessage()]);

            return self::RESULT_FAILED;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $response = Http::withToken($accessToken)
            ->timeout(15)
            ->acceptJson()
            ->post($url, ['message' => $message]);

        if ($response->successful()) {
            return self::RESULT_SENT;
        }

        if ($response->status() === 401) {
            $this->tokens->forget();
        }

        $bodyJson = $response->json();
        $errorCode = is_array($bodyJson) ? data_get($bodyJson, 'error.details.0.errorCode') : null;
        $status = is_array($bodyJson) ? data_get($bodyJson, 'error.status') : null;

        if ($errorCode === 'UNREGISTERED'
            || ($status === 'NOT_FOUND' && str_contains((string) $response->body(), 'UNREGISTERED'))) {
            return self::RESULT_INVALID_TOKEN;
        }

        Log::warning('FCM send failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return self::RESULT_FAILED;
    }

    /**
     * FCM data payload values must be strings.
     *
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[(string) $key] = (string) $value;
        }

        return $out;
    }

    private function projectIdFromCredentials(): string
    {
        $path = (string) config('push.firebase.credentials');
        if ($path === '' || ! is_readable($path)) {
            return '';
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? (string) ($data['project_id'] ?? '') : '';
    }
}
