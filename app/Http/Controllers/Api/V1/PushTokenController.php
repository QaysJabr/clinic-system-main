<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Services\Push\DevicePushTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PushTokenController extends ApiController
{
    public function register(Request $request, DevicePushTokenService $tokens): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', 'in:android,ios'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:32'],
        ]);

        $record = $tokens->register($user, $validated);

        return $this->ok([
            'id' => $record->id,
            'platform' => $record->platform,
            'registered_at' => $record->updated_at?->toIso8601String(),
        ]);
    }

    public function unregister(Request $request, DevicePushTokenService $tokens): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:512'],
        ]);

        $deleted = $tokens->unregister($user, $validated['token'] ?? null);

        return $this->ok([
            'deleted' => $deleted,
        ]);
    }
}
