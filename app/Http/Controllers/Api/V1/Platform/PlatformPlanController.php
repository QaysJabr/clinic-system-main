<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Http\Resources\Api\PlatformPlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

final class PlatformPlanController extends ApiController
{
    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->withCount('clinics')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->ok(PlatformPlanResource::collection($plans));
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = Plan::query()->create($request->planAttributes());

        return $this->created(new PlatformPlanResource($plan->loadCount('clinics')));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->planAttributes());

        return $this->ok(new PlatformPlanResource($plan->fresh()->loadCount('clinics')));
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->clinics()->exists()) {
            return response()->json([
                'message' => __('platform.plan_delete_blocked_has_clinics'),
            ], 422);
        }

        $plan->delete();

        return $this->message(__('platform.plan_deleted'));
    }

    public function toggleActive(Plan $plan): JsonResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return $this->ok(new PlatformPlanResource($plan->fresh()->loadCount('clinics')));
    }
}
