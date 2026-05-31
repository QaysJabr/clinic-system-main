<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $pageTitle = __('platform.plans_title');

        if ($request->ajax()) {
            return view('admin.plans.partials.page', compact('pageTitle'));
        }

        return view('admin.plans.index', compact('pageTitle'));
    }

    public function data(): JsonResponse
    {
        $plans = Plan::query()
            ->withCount('clinics')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->jsonSuccess(__('platform.plans_loaded'), [
            'plans' => $plans->map(fn (Plan $p) => $this->planToArray($p))->values()->all(),
        ]);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = Plan::query()->create($request->planAttributes());

        return $this->jsonSuccess(__('platform.plan_created'), [
            'plan' => $this->planToArray($plan->loadCount('clinics')),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->planAttributes());

        return $this->jsonSuccess(__('platform.plan_updated'), [
            'plan' => $this->planToArray($plan->fresh()->loadCount('clinics')),
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->clinics()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('platform.plan_delete_blocked_has_clinics'),
                'data' => [],
            ], 422);
        }

        $id = $plan->id;
        $plan->delete();

        return $this->jsonSuccess(__('platform.plan_deleted'), ['id' => $id]);
    }

    public function toggleActive(Plan $plan): JsonResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return $this->jsonSuccess($plan->is_active ? __('platform.plan_activated') : __('platform.plan_deactivated'), [
            'plan' => $this->planToArray($plan->fresh()->loadCount('clinics')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function planToArray(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price_monthly' => (string) $plan->price_monthly,
            'price_yearly' => (string) $plan->price_yearly,
            'display_monthly' => $plan->displayMonthly(),
            'display_yearly' => $plan->displayYearly(),
            'max_patients' => $plan->max_patients,
            'max_users' => $plan->max_users,
            'features' => $plan->features ?? [],
            'trial_days' => $plan->trial_days,
            'stripe_price_id' => $plan->stripe_price_id,
            'stripe_price_yearly_id' => $plan->stripe_price_yearly_id,
            'is_active' => (bool) $plan->is_active,
            'sort_order' => $plan->sort_order,
            'clinics_count' => (int) ($plan->clinics_count ?? $plan->clinics()->count()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function jsonSuccess(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
