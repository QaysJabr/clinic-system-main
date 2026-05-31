<?php

namespace App\Support;

/**
 * Central invalidation for clinic-scoped dashboard/report caches.
 */
final class ClinicCacheInvalidator
{
    public static function flush(?int $clinicId = null): void
    {
        $clinicId ??= (int) (ClinicSettings::current()->clinic_id ?? config('tenancy.default_clinic_id', 1));

        TenantCache::forget('dashboard.financial', $clinicId);
        TenantCache::forgetPrefix('dashboard.analytics', $clinicId);
        TenantCache::forgetPrefix('dashboard.operational', $clinicId);
        TenantCache::forgetPrefix('reports.data', $clinicId);
        TenantCache::forget('doctors.list', $clinicId);
    }
}
