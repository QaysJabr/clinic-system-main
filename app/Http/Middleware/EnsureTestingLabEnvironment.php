<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Testing Lab UI is only available in local/testing — hidden (404) elsewhere.
 */
final class EnsureTestingLabEnvironment
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(config('testing-lab.allowed_environments', ['local', 'testing']))) {
            abort(404);
        }

        if (! config('testing-lab.dashboard_enabled', true)) {
            abort(404);
        }

        return $next($request);
    }
}
