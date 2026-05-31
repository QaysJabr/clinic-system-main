<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use App\Support\AuthRedirect;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(AuthRedirect::homeUrl($request->user(), absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            AuditLogger::log(
                'update',
                'profile',
                $request->user()->id,
                'تأكيد البريد الإلكتروني',
                null,
                ['email_verified' => true]
            );
        }

        return redirect()->intended(AuthRedirect::homeUrl($request->user(), absolute: false).'?verified=1');
    }
}
