<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\User;

final class SaasBillingAccess
{
    public static function canManage(?User $user, ?Clinic $clinic): bool
    {
        if (! $user || ! $clinic) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return false;
        }

        if (! $user->hasRole('admin')) {
            return false;
        }

        if ((int) $user->id === (int) $clinic->owner_id) {
            return true;
        }

        return $user->can(ClinicPermissions::MANAGE_BILLING);
    }
}
