<?php

namespace App\Services\Push;

use App\Models\DevicePushToken;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class DevicePushTokenService
{
    /**
     * @param  array{token: string, platform: string, device_name?: string|null, app_version?: string|null}  $input
     */
    public function register(User $user, array $input): DevicePushToken
    {
        $token = trim((string) $input['token']);
        if ($token === '') {
            throw ValidationException::withMessages([
                'token' => [__('push.errors.token_required')],
            ]);
        }

        $platform = strtolower(trim((string) $input['platform']));
        if (! in_array($platform, DevicePushToken::platforms(), true)) {
            throw ValidationException::withMessages([
                'platform' => [__('push.errors.platform_invalid')],
            ]);
        }

        $record = DevicePushToken::query()->where('token', $token)->first();

        if ($record === null) {
            $record = new DevicePushToken(['token' => $token]);
        }

        $record->fill([
            'user_id' => $user->id,
            'clinic_id' => $user->clinic_id,
            'platform' => $platform,
            'device_name' => isset($input['device_name']) ? trim((string) $input['device_name']) : $record->device_name,
            'app_version' => isset($input['app_version']) ? trim((string) $input['app_version']) : $record->app_version,
            'last_used_at' => now(),
        ]);
        $record->save();

        return $record;
    }

    public function unregister(User $user, ?string $token = null): int
    {
        $query = DevicePushToken::query()->where('user_id', $user->id);

        if (is_string($token) && $token !== '') {
            $query->where('token', trim($token));
        }

        return $query->delete();
    }

    /**
     * Remove invalid FCM tokens after failed delivery.
     */
    public function deleteToken(string $token): void
    {
        DevicePushToken::query()->where('token', $token)->delete();
    }
}
