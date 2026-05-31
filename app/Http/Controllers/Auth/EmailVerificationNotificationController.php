<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(AuthRedirect::homeUrl($request->user(), absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        AuditLogger::log(
            'request',
            'auth',
            $request->user()->id,
            'إعادة إرسال رابط التحقق من البريد الإلكتروني',
            null,
            null
        );

        return back()->with('status', 'verification-link-sent');
    }
}
