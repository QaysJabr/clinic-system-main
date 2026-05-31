<?php

namespace App\Support;

use App\Models\User;

/**
 * المسار الافتراضي بعد تسجيل الدخول أو التحقق من البريد، حسب الدور.
 */
final class AuthRedirect
{
    public static function homeUrl(User $user, bool $absolute = false): string
    {
        if ($user->hasRole('super_admin')) {
            return route('platform.dashboard', absolute: $absolute);
        }

        if ($user->can('view dashboard')) {
            return route('dashboard', absolute: $absolute);
        }

        return route('profile.edit', absolute: $absolute);
    }
}
