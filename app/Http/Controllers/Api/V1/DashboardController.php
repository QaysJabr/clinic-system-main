<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Services\Api\MobileDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends ApiController
{
    public function __invoke(Request $request, MobileDashboardService $dashboard): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        return $this->ok($dashboard->build($user));
    }
}
