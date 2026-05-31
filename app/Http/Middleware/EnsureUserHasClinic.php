<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasClinic
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->clinic_id !== null) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'هذا الحساب غير مرتبط بعيادة.');
        }

        abort(403, 'هذا الحساب غير مرتبط بعيادة.');
    }
}

