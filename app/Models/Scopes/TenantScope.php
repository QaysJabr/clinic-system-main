<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically restricts queries to the authenticated user's clinic.
 * Bypassed for users with role `super_admin`.
 */
final class TenantScope implements Scope
{
    public static bool $enabled = true;

    public function apply(Builder $builder, Model $model): void
    {
        if (! static::$enabled) {
            return;
        }

        if (! Auth::hasUser()) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $user = Auth::user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return;
        }

        $clinicId = $user?->clinic_id ?? null;

        if ($clinicId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->getTable().'.clinic_id', $clinicId);
    }
}
