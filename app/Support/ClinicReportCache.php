<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class ClinicReportCache
{
    public static function reportDataTtl(): int
    {
        return (int) config('performance.cache.report_data_ttl', 90);
    }

    public static function dashboardFinancialTtl(): int
    {
        return (int) config('performance.cache.dashboard_financial_ttl', 60);
    }

    public static function dashboardAnalyticsTtl(): int
    {
        return (int) config('performance.cache.dashboard_analytics_ttl', 120);
    }

    public static function dashboardOperationalTtl(): int
    {
        return (int) config('performance.cache.dashboard_operational_ttl', 45);
    }

    public static function reportDataKey(Request $request): string
    {
        $hash = hash('sha256', (string) $request->getQueryString());

        return TenantCache::key("reports.data.v3.{$hash}");
    }

    /**
     * Cache scalar/array metrics only; live lists are merged after read.
     *
     * @template T
     *
     * @param  Closure(): array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public static function rememberReportData(Request $request, Closure $callback): array
    {
        $key = self::reportDataKey($request);
        $cached = Cache::get($key);

        if (is_array($cached)) {
            return self::hydrateReportPayload(array_merge($cached, self::freshReportLists()));
        }

        $full = $callback();
        $storable = self::extractStorableMetrics($full);
        Cache::put($key, $storable, self::reportDataTtl());

        return $full;
    }

    public static function dashboardFinancialKey(): string
    {
        return TenantCache::key('dashboard.financial');
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function rememberDashboardFinancial(Closure $callback): mixed
    {
        return Cache::remember(
            self::dashboardFinancialKey(),
            self::dashboardFinancialTtl(),
            $callback
        );
    }

    public static function dashboardAnalyticsKey(?int $doctorId): string
    {
        $scope = $doctorId ?? 'all';
        $locale = app()->getLocale();

        return TenantCache::key("dashboard.analytics.{$scope}.{$locale}");
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function rememberDashboardAnalytics(?int $doctorId, Closure $callback): mixed
    {
        return Cache::remember(
            self::dashboardAnalyticsKey($doctorId),
            self::dashboardAnalyticsTtl(),
            $callback
        );
    }

    public static function dashboardOperationalKey(bool $personal, ?int $doctorId): string
    {
        $scope = $personal ? 'doctor.'.$doctorId : 'clinic';

        return TenantCache::key("dashboard.operational.v3.{$scope}");
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function rememberDashboardOperational(bool $personal, ?int $doctorId, Closure $callback): mixed
    {
        return Cache::remember(
            self::dashboardOperationalKey($personal, $doctorId),
            self::dashboardOperationalTtl(),
            $callback
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function extractStorableMetrics(array $payload): array
    {
        $exclude = [
            'recentPatients',
            'recentAppointments',
            'recentInvoices',
            'recentPayments',
            'clinic',
            'cur',
            'generatedAt',
        ];

        $out = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, $exclude, true)) {
                continue;
            }
            if ($value instanceof Collection) {
                $out[$key] = self::serializeCollection($value);

                continue;
            }
            if (is_object($value)) {
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function serializeCollection(Collection $collection): array
    {
        return $collection
            ->map(function (mixed $item): array {
                if ($item instanceof Model) {
                    return $item->toArray();
                }

                return (array) $item;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function hydrateReportPayload(array $payload): array
    {
        foreach ([
            'expenseCategoriesForReportFilter',
            'expenseSummaryByCategory',
            'expenseSummaryByCategoryAllTime',
        ] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                continue;
            }
            $payload[$key] = collect($payload[$key])->map(fn (array $row): object => (object) $row);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private static function freshReportLists(): array
    {
        return [
            'recentPatients' => Patient::query()
                ->select(['id', 'full_name', 'phone', 'created_at'])
                ->latest()
                ->take(5)
                ->get(),
            'recentAppointments' => Appointment::query()
                ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'created_at'])
                ->with(['patient:id,full_name', 'doctor:id,full_name'])
                ->latest()
                ->take(5)
                ->get(),
            'recentInvoices' => Invoice::query()
                ->select(['id', 'invoice_number', 'patient_id', 'total', 'status', 'created_at'])
                ->with(['patient:id,full_name'])
                ->latest()
                ->take(5)
                ->get(),
            'recentPayments' => Payment::query()
                ->select(['id', 'invoice_id', 'amount', 'payment_method', 'payment_date', 'created_at'])
                ->with(['invoice:id,invoice_number,patient_id', 'invoice.patient:id,full_name'])
                ->latest()
                ->take(5)
                ->get(),
        ];
    }
}
