<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OAuth2 access token for Firebase Cloud Messaging (service account JWT).
 */
final class FcmAccessTokenProvider
{
    private const CACHE_KEY = 'fcm_oauth_access_token';

    public function get(): string
    {
        return Cache::remember(self::CACHE_KEY, 3300, function (): string {
            return $this->requestNewToken();
        });
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function requestNewToken(): string
    {
        $credentials = $this->loadCredentials();
        $jwt = $this->buildJwt($credentials);

        $response = Http::asForm()
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('FCM OAuth failed: '.$response->body());
        }

        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('FCM OAuth returned no access_token.');
        }

        return $token;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadCredentials(): array
    {
        $path = (string) config('push.firebase.credentials');
        if ($path === '' || ! is_readable($path)) {
            throw new RuntimeException('Firebase credentials file is missing or not readable.');
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            throw new RuntimeException('Firebase credentials JSON is invalid.');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function buildJwt(array $credentials): string
    {
        $clientEmail = (string) ($credentials['client_email'] ?? '');
        $privateKey = (string) ($credentials['private_key'] ?? '');
        if ($clientEmail === '' || $privateKey === '') {
            throw new RuntimeException('Firebase credentials missing client_email or private_key.');
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ], JSON_THROW_ON_ERROR));

        $unsigned = $header.'.'.$payload;
        $signature = '';
        $key = openssl_pkey_get_private($privateKey);
        if ($key === false) {
            throw new RuntimeException('Invalid Firebase private key.');
        }

        openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256);

        return $unsigned.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
