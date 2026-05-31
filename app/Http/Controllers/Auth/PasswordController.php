<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $uid = $request->user()->id;

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLogger::log(
            'update',
            'auth',
            $uid,
            'تغيير كلمة المرور من إعدادات الحساب',
            null,
            ['password_changed' => true]
        );

        return back()->with('status', 'password-updated');
    }
}
