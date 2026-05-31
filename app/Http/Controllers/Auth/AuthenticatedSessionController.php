<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Security\SuspiciousLoginService;
use App\Services\Security\TwoFactorService;
use App\Support\AuditLogger;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $request->session()->forget(TwoFactorService::SESSION_VERIFIED);

        app(SuspiciousLoginService::class)->recordSuccess($user, $request);

        AuditLogger::security(
            'login',
            'auth',
            $user->id,
            'تسجيل دخول: '.$user->email,
            null,
            ['email' => $user->email]
        );

        if (config('security.two_factor.enabled', false)) {
            $twoFactor = app(TwoFactorService::class);
            if ($twoFactor->isEnabled($user) && ! $twoFactor->hasTrustedDevice($request, $user)) {
                return redirect()->route('two-factor.challenge');
            }

            if ($twoFactor->isEnabled($user)) {
                $twoFactor->markVerified($request);
            }

            if ($twoFactor->mustEnforce($user)) {
                return redirect()->route('two-factor.setup');
            }
        }

        $canDashboard = $user->can('view dashboard');

        $default = AuthRedirect::homeUrl($user, absolute: false);

        $intended = $request->session()->get('url.intended');
        if (is_string($intended) && str_contains($intended, '/dashboard') && ! $canDashboard) {
            $request->session()->forget('url.intended');
        }
        if ($user->hasRole('super_admin') && is_string($intended) && ! str_contains($intended, '/platform')) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended($default);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user !== null) {
            AuditLogger::log(
                'logout',
                'auth',
                $user->id,
                'تسجيل خروج: '.$user->email,
                null,
                ['email' => $user->email]
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
