<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureApiPlatformOwner
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isPlatformOwner()) {
            return response()->json([
                'message' => __('api.errors.platform_only'),
                'code' => 'platform_only',
            ], 403);
        }

        return $next($request);
    }
}
