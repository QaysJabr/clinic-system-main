<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

final class TenantValidation
{
    public static function clinicIdForRules(): int
    {
        $user = Auth::user();

        return (int) ($user?->clinic_id ?? config('tenancy.default_clinic_id'));
    }
}
