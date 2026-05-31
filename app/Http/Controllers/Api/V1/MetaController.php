<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;

final class MetaController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return $this->ok([
            'api_version' => config('mobile.api_version', '1.0.0'),
            'min_app_version' => config('mobile.min_app_version', '1.0.0'),
            'app_name' => config('app.name'),
            'locales' => ['ar', 'en'],
            'default_locale' => config('app.locale', 'ar'),
            'auth' => [
                'type' => 'bearer',
                'header' => 'Authorization',
                'two_factor_supported' => true,
            ],
            'push' => [
                'enabled' => (bool) config('push.enabled', false),
                'register_path' => '/api/v1/push/register',
                'platforms' => ['android', 'ios'],
            ],
        ]);
    }
}
