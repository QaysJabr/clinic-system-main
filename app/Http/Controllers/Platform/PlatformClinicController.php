<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Services\InAppNotificationService;
use App\Services\Platform\PlatformManualPaymentNotifier;
use App\Services\PlatformDashboardService;
use App\Services\SubscriptionPaymentRecorder;
use App\Support\PlatformClinicIndexQuery;
use App\Support\PlatformClinicPresentation;
use App\Support\PlatformStripePresentation;
use App\Support\PlatformSubscriptionPaymentQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Laravel\Cashier\Subscription;

final class PlatformClinicController extends Controller
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotifications,
        private readonly SubscriptionPaymentRecorder $paymentRecorder,
        private readonly PlatformManualPaymentNotifier $paymentNotifier,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $clinics = PlatformClinicIndexQuery::fromRequest($request)
            ->paginate(20)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('platform.clinics_loaded'),
                'data' => [
                    'html' => view('platform.clinics.partials.table', compact('clinics'))->render(),
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        if ($request->ajax()) {
            $pageTitle = __('platform.clinics_title');

            return view('platform.clinics.partials.page', compact('clinics', 'pageTitle'));
        }

        return view('platform.clinics.index', compact('clinics'));
    }

    public function subscription(Request $request, Clinic $clinic): View
    {
        $plans = Plan::query()->orderBy('sort_order')->orderBy('id')->get();
        $pageTitle = __('platform.subscription').' · '.$clinic->name;

        $clinic->load(['owner:id,name,email', 'plan:id,name']);
        $clinic->loadSum([
            'subscriptionPayments as total_paid' => function ($q): void {
                $q->where('status', SubscriptionPayment::STATUS_SUCCEEDED);
            },
        ], 'amount');

        $stripeSubscription = $clinic->subscription('default');
        $stripeStatusLabel = PlatformStripePresentation::statusLabel($clinic);

        $subscriptionTier = PlatformClinicPresentation::subscriptionTier($clinic);
        $subscriptionTierLabel = PlatformClinicPresentation::tierLabel($subscriptionTier);
        $subscriptionTierBadgeClass = PlatformClinicPresentation::tierBadgeClass($subscriptionTier);

        $paymentFilters = [
            'paid_from' => $request->input('paid_from', ''),
            'paid_to' => $request->input('paid_to', ''),
        ];

        $recentPayments = PlatformSubscriptionPaymentQuery::succeededFromRequest($request, $clinic->id)
            ->limit(100)
            ->get();

        $vars = compact(
            'clinic',
            'plans',
            'pageTitle',
            'stripeSubscription',
            'stripeStatusLabel',
            'subscriptionTier',
            'subscriptionTierLabel',
            'subscriptionTierBadgeClass',
            'recentPayments',
            'paymentFilters',
        );

        if ($request->ajax()) {
            return view('platform.clinics.partials.subscription-inner', $vars);
        }

        return view('platform.clinics.subscription', $vars);
    }

    public function edit(Clinic $clinic): RedirectResponse
    {
        return redirect()->route('platform.clinics.subscription', $clinic);
    }

    public function update(Request $request, Clinic $clinic): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'in:0,1'],
            'subscription_status' => ['required', 'in:active,expired'],
            'subscription_expires_at' => ['nullable', 'date'],
            'plan_id' => ['nullable', 'exists:plans,id'],
        ]);

        $expires = $validated['subscription_expires_at'] ?? null;
        if ($expires === '' || $expires === null) {
            $expires = null;
        }

        $clinic->forceFill([
            'is_active' => (bool) (int) $validated['is_active'],
            'subscription_status' => $validated['subscription_status'],
            'subscription_expires_at' => $expires,
            'plan_id' => $validated['plan_id'] ?: null,
        ])->save();

        PlatformDashboardService::forgetMetricsCache();

        $this->inAppNotifications->notifyPlatformClinicManaged(
            $clinic->fresh(),
            __('platform.notification_clinic_updated_title'),
            __('platform.notification_clinic_updated_body', ['name' => $clinic->name])
        );

        return redirect()->route('platform.clinics.subscription', $clinic)->with('success', __('platform.clinic_updated'));
    }

    public function activate(Clinic $clinic): RedirectResponse|JsonResponse
    {
        $clinic->forceFill([
            'is_active' => true,
            'subscription_status' => Clinic::STATUS_ACTIVE,
        ])->save();

        $this->inAppNotifications->notifyPlatformClinicManaged(
            $clinic->fresh(),
            __('platform.notification_clinic_activated_title'),
            __('platform.notification_clinic_activated_body', ['name' => $clinic->name])
        );

        PlatformDashboardService::forgetMetricsCache();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('platform.clinic_activated'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return redirect()->route('platform.clinics.index')->with('success', __('platform.clinic_activated'));
    }

    public function suspend(Clinic $clinic): RedirectResponse|JsonResponse
    {
        $clinic->forceFill([
            'is_active' => false,
        ])->save();

        $this->inAppNotifications->notifyPlatformClinicManaged(
            $clinic->fresh(),
            __('platform.notification_clinic_suspended_title'),
            __('platform.notification_clinic_suspended_body', ['name' => $clinic->name])
        );

        PlatformDashboardService::forgetMetricsCache();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('platform.clinic_suspended'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return redirect()->route('platform.clinics.index')->with('success', __('platform.clinic_suspended'));
    }

    public function recordPayment(Request $request, Clinic $clinic): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'subscription_expires_at' => ['nullable', 'date'],
        ]);

        $paidAt = isset($validated['paid_at']) ? Carbon::parse($validated['paid_at']) : now();

        $payment = $this->paymentRecorder->recordManual(
            $clinic,
            (float) $validated['amount'],
            $paidAt,
            $validated['notes'] ?? null
        );

        $expires = $validated['subscription_expires_at'] ?? null;
        if ($expires === '' || $expires === null) {
            $expires = $clinic->subscription_expires_at;
        }

        $clinic->forceFill([
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'is_active' => true,
            'subscription_expires_at' => $expires,
        ])->save();

        $clinic = $clinic->fresh(['owner:id,name,email']);

        $amt = number_format((float) $validated['amount'], 2);
        $this->inAppNotifications->notifyPlatformClinicManaged(
            $clinic,
            __('platform.notification_payment_recorded_title'),
            __('platform.notification_payment_recorded_body', ['name' => $clinic->name, 'amount' => $amt])
        );

        $this->paymentNotifier->notifyOwner($clinic, $payment);

        return redirect()->route('platform.clinics.subscription', $clinic)->with('success', __('platform.payment_recorded'));
    }
}
