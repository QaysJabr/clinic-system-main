<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Plan;
use App\Support\SaasBillingAccess;
use Illuminate\View\View;

final class PricingController extends Controller
{
    public function __invoke(): View
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $user = auth()->user();
        $clinic = $user?->clinic_id
            ? Clinic::query()->with('plan')->find($user->clinic_id)
            : null;

        $canManageBilling = SaasBillingAccess::canManage($user, $clinic);
        $currentPlanId = $clinic?->plan_id;
        $allowPromotionCodes = (bool) config('saas.checkout.allow_promotion_codes', true);

        return view('saas.pricing', compact(
            'plans',
            'clinic',
            'canManageBilling',
            'currentPlanId',
            'allowPromotionCodes',
        ));
    }
}
