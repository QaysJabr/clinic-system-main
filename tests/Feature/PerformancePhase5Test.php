<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Support\ClinicCacheInvalidator;
use App\Support\ClinicReportCache;
use App\Support\MonthBucket;
use App\Support\TenantCache;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformancePhase5Test extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_cache_remembers_and_forgets(): void
    {
        Cache::flush();

        $value = TenantCache::remember('test.segment', 60, fn () => 'ok', 1);
        $this->assertSame('ok', $value);

        TenantCache::forget('test.segment', 1);

        $this->assertNull(Cache::get(TenantCache::key('test.segment', 1)));
    }

    public function test_cache_invalidator_clears_dashboard_financial_key(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Cache::flush();

        Cache::put(ClinicReportCache::dashboardFinancialKey(), ['profit' => 1], 120);
        ClinicCacheInvalidator::flush();

        $this->assertNull(Cache::get(ClinicReportCache::dashboardFinancialKey()));
    }

    public function test_month_bucket_counts_returns_aligned_series(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $ymList = [now()->format('Y-m')];
        $counts = MonthBucket::counts(
            Patient::query(),
            'created_at',
            now()->startOfMonth(),
            now()->endOfMonth(),
            $ymList,
        );

        $this->assertCount(1, $counts);
        $this->assertIsInt($counts[0]);
    }
}
