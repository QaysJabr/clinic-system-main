<?php

namespace App\Http\Middleware;

use App\Services\Security\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTwoFactorVerified
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.two_factor.enabled', false)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($this->twoFactor->mustEnforce($user) && ! $this->twoFactor->isEnabled($user)) {
            if (! $request->routeIs('two-factor.*', 'logout', 'profile.*')) {
                return redirect()->route('two-factor.setup');
            }

            return $next($request);
        }

        if (! $this->twoFactor->isEnabled($user)) {
            return $next($request);
        }

        if ($this->twoFactor->hasTrustedDevice($request, $user)) {
            $this->twoFactor->markVerified($request);

            return $next($request);
        }

        if ($this->twoFactor->isVerified($request)) {
            return $next($request);
        }

        if ($request->routeIs('two-factor.*', 'logout')) {
            return $next($request);
        }

        return redirect()->route('two-factor.challenge');
    }
}
