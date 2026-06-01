<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يضمن أن اشتراك العيادة نشط (يشمل سجلات clinic_subscriptions والحقول القديمة على clinics).
 */
final class CheckSubscription
{
    /**
     * @param  Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $clinicId = $user->clinic_id;

        if ($clinicId === null) {
            abort(403, 'لم يتم ربط حسابك بعيادة.');
        }

        $clinic = Clinic::query()->find($clinicId);

        if (! $clinic || ! app(SubscriptionService::class)->isActive($clinic)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'انتهى اشتراك العيادة أو أنه غير نشط.',
                    'code' => 'subscription_inactive',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            return redirect()
                ->route('saas.billing')
                ->with('error', 'انتهى اشتراك العيادة أو أنه غير نشط. جدّد الاشتراك للمتابعة.');
        }

        return $next($request);
    }
}
