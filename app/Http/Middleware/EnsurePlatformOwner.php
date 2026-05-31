<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePlatformOwner
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('super_admin')) {
            abort(403);
        }

        $ownerEmail = config('platform.owner_email');

        if (app()->environment('production')) {
            if (! is_string($ownerEmail) || $ownerEmail === '') {
                abort(503, 'Platform owner email is not configured (PLATFORM_OWNER_EMAIL).');
            }
        }

        if (is_string($ownerEmail) && $ownerEmail !== '' && strcasecmp($user->email, $ownerEmail) !== 0) {
            abort(403);
        }

        if ($user->clinic_id !== null) {
            abort(403);
        }

        return $next($request);
    }
}
