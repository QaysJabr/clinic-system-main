<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * واجهة الموبايل للعيادات فقط — ليس لحسابات مدير المنصّة.
 */
final class EnsureApiClinicUser
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return response()->json([
                'message' => __('api.errors.platform_account'),
                'code' => 'platform_account',
            ], 403);
        }

        if ($user->clinic_id === null) {
            return response()->json([
                'message' => __('api.errors.no_clinic'),
                'code' => 'no_clinic',
            ], 403);
        }

        return $next($request);
    }
}
