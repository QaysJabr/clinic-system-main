<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\PlatformClinicResource;
use App\Http\Resources\Api\PlatformPlanResource;
use App\Http\Resources\Api\SubscriptionPaymentResource;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Services\InAppNotificationService;
use App\Services\Platform\PlatformManualPaymentNotifier;
use App\Services\PlatformDashboardService;
use App\Services\SubscriptionPaymentRecorder;
use App\Support\PlatformClinicIndexQuery;
use App\Support\PlatformSubscriptionPaymentQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class PlatformClinicController extends ApiController
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotifications,
        private readonly SubscriptionPaymentRecorder $paymentRecorder,
        private readonly PlatformManualPaymentNotifier $paymentNotifier,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = PlatformClinicIndexQuery::fromRequest($request)
            ->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        return $this->ok(
            PlatformClinicResource::collection($paginator->items()),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }

    public function show(Request $request, Clinic $clinic): JsonResponse
    {
        $clinic->load(['owner:id,name,email', 'plan:id,name']);
        $clinic->loadSum([
            'subscriptionPayments as total_paid' => function ($q): void {
                $q->where('status', SubscriptionPayment::STATUS_SUCCEEDED);
            },
        ], 'amount');

        $recentPayments = PlatformSubscriptionPaymentQuery::succeededFromRequest($request, $clinic->id)
            ->limit(50)
            ->get();

        $plans = Plan::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'is_active']);

        return $this->ok([
            'clinic' => new PlatformClinicResource($clinic),
            'recent_payments' => SubscriptionPaymentResource::collection($recentPayments),
            'plans' => PlatformPlanResource::collection($plans),
        ]);
    }

    public function update(Request $request, Clinic $clinic): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'subscription_status' => ['required', 'in:active,expired'],
            'subscription_expires_at' => ['nullable', 'date'],
            'plan_id' => ['nullable', 'exists:plans,id'],
        ]);

        $expires = $validated['subscription_expires_at'] ?? null;
        if ($expires === '' || $expires === null) {
            $expires = null;
        }

        $clinic->forceFill([
            'is_active' => (bool) $validated['is_active'],
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

        return $this->ok(new PlatformClinicResource($clinic->fresh(['owner:id,name,email', 'plan:id,name'])));
    }

    public function activate(Clinic $clinic): JsonResponse
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

        return $this->ok(new PlatformClinicResource($clinic->fresh(['owner:id,name,email', 'plan:id,name'])));
    }

    public function suspend(Clinic $clinic): JsonResponse
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

        return $this->ok(new PlatformClinicResource($clinic->fresh(['owner:id,name,email', 'plan:id,name'])));
    }

    public function recordPayment(Request $request, Clinic $clinic): JsonResponse
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

        PlatformDashboardService::forgetMetricsCache();

        return $this->ok([
            'clinic' => new PlatformClinicResource($clinic->fresh(['owner:id,name,email', 'plan:id,name'])),
            'payment' => new SubscriptionPaymentResource($payment),
        ]);
    }
}
