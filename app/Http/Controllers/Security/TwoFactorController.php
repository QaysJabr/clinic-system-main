<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\TwoFactorService;
use App\Support\AuditLogger;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

final class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
    ) {}

    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        if ($this->twoFactor->isEnabled($user)) {
            return redirect()->route('profile.edit');
        }

        $secret = $request->session()->get('two_factor.pending_secret')
            ?? $this->twoFactor->generateSecret();

        $request->session()->put('two_factor.pending_secret', $secret);

        $pageTitle = __('security.two_factor_title');
        $viewData = [
            'qrSvg' => $this->twoFactor->qrSvg($user, $secret),
            'secret' => $secret,
            'pageTitle' => $pageTitle,
        ];

        if ($request->ajax()) {
            return view('security.two-factor.partials.setup', $viewData);
        }

        return view('security.two-factor.setup', $viewData);
    }

    public function confirmSetup(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = (string) $request->session()->get('two_factor.pending_secret');
        abort_if($secret === '', 422);

        $google2fa = new Google2FA;
        if (! $google2fa->verifyKey($secret, preg_replace('/\s+/', '', $request->input('code')) ?? '')) {
            return back()->withErrors(['code' => __('security.two_factor_invalid_code')]);
        }

        $plainCodes = $this->twoFactor->generateRecoveryCodes();
        $this->twoFactor->confirmSetup($user, $secret, $plainCodes);
        $request->session()->forget('two_factor.pending_secret');
        $this->twoFactor->markVerified($request);

        AuditLogger::security('two_factor_enabled', 'auth', $user->id, 'تفعيل المصادقة الثنائية');

        return redirect()
            ->route('two-factor.recovery-codes')
            ->with('recovery_codes', $plainCodes);
    }

    public function recoveryCodes(Request $request): View
    {
        $codes = $request->session()->get('recovery_codes', []);
        $pageTitle = __('security.two_factor_recovery_title');

        $viewData = [
            'codes' => $codes,
            'pageTitle' => $pageTitle,
        ];

        if ($request->ajax()) {
            return view('security.two-factor.partials.recovery-codes', $viewData);
        }

        return view('security.two-factor.recovery-codes', $viewData);
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $this->twoFactor->isEnabled($user)) {
            return redirect()->route('dashboard');
        }

        if ($this->twoFactor->isVerified($request) || $this->twoFactor->hasTrustedDevice($request, $user)) {
            return redirect()->intended(AuthRedirect::homeUrl($user, absolute: false));
        }

        return view('security.two-factor.challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $request->validate([
            'code' => ['required', 'string'],
            'trust_device' => ['nullable', 'boolean'],
        ]);

        $code = (string) $request->input('code');

        $valid = $this->twoFactor->verifyCode($user, $code)
            || $this->twoFactor->consumeRecoveryCode($user, $code);

        if (! $valid) {
            AuditLogger::security('two_factor_failed', 'auth', $user->id, 'فشل التحقق الثنائي');

            return back()->withErrors(['code' => __('security.two_factor_invalid_code')]);
        }

        $this->twoFactor->markVerified($request);

        if ($request->boolean('trust_device')) {
            $this->twoFactor->trustDevice($request, $user);
        }

        AuditLogger::security('two_factor_passed', 'auth', $user->id, 'نجاح التحقق الثنائي');

        return redirect()->intended(AuthRedirect::homeUrl($user, absolute: false));
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $request->validate(['password' => ['required', 'current_password']]);

        $this->twoFactor->disable($user);
        $request->session()->forget(TwoFactorService::SESSION_VERIFIED);

        AuditLogger::security('two_factor_disabled', 'auth', $user->id, 'إيقاف المصادقة الثنائية');

        return redirect()->route('profile.edit')->with('success', __('security.two_factor_disabled'));
    }
}
