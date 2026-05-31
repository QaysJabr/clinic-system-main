<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\UserResource;
use App\Services\Api\MobileAuthService;
use App\Services\Push\DevicePushTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AuthController extends ApiController
{
    public function login(Request $request, MobileAuthService $auth): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'two_factor_code' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $result = $auth->login($request, $validated);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $meta = [];
            if (isset($errors['two_factor_code'])) {
                $meta['two_factor_required'] = true;
            }

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $errors,
                'meta' => $meta,
            ], $e->status ?? 422);
        }

        return $this->created([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_at' => $result['expires_at'],
            'user' => new UserResource($result['user']),
        ]);
    }

    public function logout(Request $request, MobileAuthService $auth, DevicePushTokenService $pushTokens): JsonResponse
    {
        $user = $request->user();
        if ($user !== null) {
            $fcmToken = $request->input('fcm_token');
            if (is_string($fcmToken) && $fcmToken !== '') {
                $pushTokens->unregister($user, $fcmToken);
            }

            $auth->logout($user, $request->bearerToken());
        }

        return $this->message(__('api.auth_logged_out'));
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->loadMissing(['clinic.plan']);

        return $this->ok(new UserResource($user));
    }
}
