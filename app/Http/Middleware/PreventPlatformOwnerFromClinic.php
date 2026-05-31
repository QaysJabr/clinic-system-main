<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreventPlatformOwnerFromClinic
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('super_admin')) {
            if ($request->expectsJson()) {
                abort(403, 'حساب المنصّة لا يمكنه دخول صفحات العيادة.');
            }

            return redirect()->route('platform.dashboard');
        }

        return $next($request);
    }
}

