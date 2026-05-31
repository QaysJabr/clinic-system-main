<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Boot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToClinic
{
    /**
     * Tenant global scope is registered in {@see \App\Providers\AppServiceProvider}.
     */
    #[Boot]
    protected static function assignDefaultClinicIdWhenCreating(): void
    {
        static::creating(function (Model $model): void {
            if (($model->getAttribute('clinic_id') ?? null) !== null && $model->getAttribute('clinic_id') !== '') {
                return;
            }

            $default = config('tenancy.default_clinic_id');

            $user = Auth::user();

            $clinicId = $user?->clinic_id ?? $default;

            $model->setAttribute('clinic_id', $clinicId);
        });
    }
}
