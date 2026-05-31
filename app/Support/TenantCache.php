<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Tenant-scoped cache keys for multi-clinic SaaS.
 */
final class TenantCache
{
    public static function key(string $segment, ?int $clinicId = null): string
    {
        $clinicId ??= (int) ClinicSettings::current()->clinic_id;

        return "clinic.{$clinicId}.{$segment}";
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function remember(string $segment, int $ttlSeconds, Closure $callback, ?int $clinicId = null): mixed
    {
        return Cache::remember(self::key($segment, $clinicId), $ttlSeconds, $callback);
    }

    public static function forget(string $segment, ?int $clinicId = null): void
    {
        Cache::forget(self::key($segment, $clinicId));
    }

    public static function forgetPrefix(string $prefix, ?int $clinicId = null): void
    {
        $clinicId ??= (int) ClinicSettings::current()->clinic_id;
        $fullPrefix = "clinic.{$clinicId}.{$prefix}";

        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getStore()->connection();
                $keys = $redis->keys(config('cache.prefix').$fullPrefix.'*');
                foreach ($keys as $key) {
                    $redis->del($key);
                }

                return;
            } catch (\Throwable) {
                // fall through
            }
        }

        Cache::forget($fullPrefix);
    }
}
