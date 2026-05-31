<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Saas\CheckoutPlanRequest;
use App\Models\Clinic;
use App\Models\ClinicSubscription;
use App\Models\Plan;
use App\Services\Saas\ClinicBillingPageService;
use App\Support\PlanDisplay;
use App\Services\Saas\ClinicCheckoutService;
use App\Services\SubscriptionService;
use App\Support\AuditLogger;
use App\Support\SaasBillingAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class BillingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ClinicBillingPageService $billingPage,
        private readonly ClinicCheckoutService $checkoutService,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();
        $clinic = $user->clinic_id
            ? Clinic::query()->with(['plan', 'latestClinicSubscription.plan', 'owner'])->find($user->clinic_id)
            : null;
        $subscription = $clinic?->subscription('default');
        $plan = $clinic?->plan;
        $clinicSubscription = $clinic?->latestClinicSubscription;

        $pageData = $this->billingPage->build($clinic);

        $canManageBilling = SaasBillingAccess::canManage($user, $clinic);

        $viewData = array_merge($pageData, [
            'clinic' => $clinic,
            'subscription' => $subscription,
            'plan' => $plan,
            'clinicSubscription' => $clinicSubscription,
            'canOwnerBilling' => $canManageBilling,
            'canManageBilling' => $canManageBilling,
            'pageTitle' => __('saas.page_title'),
            'whatsappPhone' => (string) config('saas.support.whatsapp', ''),
        ]);

        if ($request->ajax()) {
            return view('saas.partials.billing', $viewData);
        }

        return view('saas.billing', $viewData);
    }

    public function pdfStatement(Request $request): SymfonyResponse
    {
        $user = $request->user();
        abort_unless($user && $user->clinic_id, 403);

        $clinic = Clinic::query()
            ->with(['plan', 'latestClinicSubscription.plan', 'owner'])
            ->findOrFail((int) $user->clinic_id);

        $pageData = $this->billingPage->build($clinic);

        AuditLogger::log(
            'export',
            'billing',
            $clinic->id,
            __('saas.audit_export_pdf', ['clinic' => $clinic->name]),
            null,
            null,
            Clinic::class,
        );

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/u', '_', $clinic->name) ?: 'clinic';

        $pdf = Pdf::loadView('saas.pdf.billing-statement', array_merge($pageData, [
            'clinic' => $clinic,
            'plan' => $clinic->plan,
        ]))->setPaper('a4', 'portrait');

        return $pdf->download(__('saas.pdf_download_prefix').'-'.$safeName.'.pdf');
    }

    public function checkout(CheckoutPlanRequest $request, Plan $plan): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return $this->checkoutErrorResponse($request, __('saas.super_admin_no_subscription'));
        }

        if (! $user->hasRole('admin')) {
            abort(403);
        }

        if (! $plan->is_active) {
            return $this->checkoutErrorResponse($request, __('saas.plan_unavailable'));
        }

        $clinic = Clinic::query()->find((int) $user->clinic_id);
        if (! $clinic) {
            abort(403, __('saas.not_linked_to_clinic'));
        }

        if (! SaasBillingAccess::canManage($user, $clinic)) {
            abort(403, __('saas.only_owner_checkout'));
        }

        try {
            $result = $this->checkoutService->checkout(
                $clinic,
                $plan,
                $request->validated('billing_cycle'),
                $request->validated('promotion_code'),
            );
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? __('saas.checkout_failed');

            return $this->checkoutErrorResponse($request, (string) $message);
        }

        if ($result['mode'] === 'stripe' && isset($result['url'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'mode' => 'stripe',
                        'url' => $result['url'],
                    ],
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return redirect()->away($result['url']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'mode' => $result['mode'],
                    'billing' => $result['billing'] ?? $this->billingPayload($clinic),
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return redirect()
            ->route('saas.billing')
            ->with('success', $result['message']);
    }

    public function cancelSubscription(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin') || $user->hasRole('super_admin')) {
            abort(403);
        }

        $clinic = Clinic::query()->find((int) $user->clinic_id);
        if (! $clinic) {
            abort(403, __('saas.not_linked_to_clinic'));
        }

        if (! SaasBillingAccess::canManage($user, $clinic)) {
            abort(403, __('saas.only_owner_cancel_subscription'));
        }

        $cashier = $clinic->subscription('default');
        if ($cashier && ! $cashier->ended()) {
            try {
                $cashier->cancelNow();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $latest = $clinic->clinicSubscriptions()
            ->whereIn('status', [ClinicSubscription::STATUS_ACTIVE, ClinicSubscription::STATUS_TRIAL])
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            app(SubscriptionService::class)->cancel($latest);
        }

        $clinic->refresh()->load(['plan', 'latestClinicSubscription.plan']);

        return response()->json([
            'success' => true,
            'message' => __('saas.subscription_cancelled_local'),
            'data' => [
                'billing' => $this->billingPayload($clinic, $clinic->latestClinicSubscription),
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') || $user->hasRole('super_admin')) {
            abort(403);
        }

        $clinic = Clinic::query()->findOrFail((int) $user->clinic_id);

        if (! SaasBillingAccess::canManage($user, $clinic)) {
            abort(403);
        }

        if (! $clinic->hasStripeId()) {
            return redirect()
                ->route('saas.pricing')
                ->with('error', __('saas.complete_subscription_for_portal'));
        }

        return $clinic->redirectToBillingPortal(route('saas.billing'));
    }

    private function checkoutErrorResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => [],
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        return redirect()->route('saas.pricing')->with('error', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function billingPayload(Clinic $clinic, ?ClinicSubscription $clinicSubscription = null): array
    {
        $clinic->loadMissing(['plan', 'latestClinicSubscription.plan']);
        $subRow = $clinicSubscription ?? $clinic->latestClinicSubscription;

        $expires = $clinic->subscription_expires_at;
        $daysRemaining = $expires ? (int) ceil(now()->diffInDays($expires, false)) : null;

        $stripeSub = $clinic->subscription('default');

        $subscriptionStatusExpired =
            $clinic->subscription_status === Clinic::STATUS_EXPIRED
            || ($expires && $expires->isPast())
            || ($daysRemaining !== null && $daysRemaining < 0);

        return [
            'plan_name' => PlanDisplay::localizedName($clinic->plan, $clinic->subscription_plan),
            'subscription_status' => $clinic->subscription_status,
            'subscription_status_label' => $this->clinicStatusLabel($clinic),
            'subscription_status_expired' => $subscriptionStatusExpired,
            'clinic_subscription_status' => $subRow?->status,
            'clinic_subscription_status_label' => $subRow ? $this->clinicSubscriptionStatusLabel($subRow->status) : null,
            'billing_cycle' => $subRow?->billing_cycle,
            'amount' => $subRow?->amount !== null ? (string) $subRow->amount : null,
            'expires_at' => $expires?->format('d/m/Y H:i'),
            'days_remaining' => $daysRemaining,
            'stripe_status' => $stripeSub?->stripe_status,
            'stripe_status_label' => ClinicBillingPageService::stripeSubscriptionStatusLabel($stripeSub?->stripe_status),
        ];
    }

    private function clinicStatusLabel(Clinic $clinic): string
    {
        if ($clinic->subscription_status === Clinic::STATUS_EXPIRED) {
            return __('saas.status_expired');
        }

        if ($clinic->subscription_expires_at && $clinic->subscription_expires_at->isPast()) {
            return __('saas.status_expired');
        }

        if (app(SubscriptionService::class)->isActive($clinic)) {
            return __('saas.status_active');
        }

        return __('saas.status_inactive');
    }

    private function clinicSubscriptionStatusLabel(string $status): string
    {
        return match ($status) {
            ClinicSubscription::STATUS_ACTIVE => __('saas.clinic_sub_active'),
            ClinicSubscription::STATUS_TRIAL => __('saas.clinic_sub_trial'),
            ClinicSubscription::STATUS_CANCELED => __('saas.clinic_sub_canceled'),
            ClinicSubscription::STATUS_EXPIRED => __('saas.clinic_sub_expired'),
            default => $status,
        };
    }
}
