<?php

namespace App\Support;

use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Database-agnostic month bucketing for chart aggregates (avoids loading full row sets).
 */
final class MonthBucket
{
    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $ymList
     * @return list<int>
     */
    public static function counts(
        Builder $query,
        string $dateColumn,
        Carbon $start,
        Carbon $end,
        array $ymList,
    ): array {
        $bucket = self::bucketExpression($dateColumn);
        $rows = (clone $query)
            ->whereBetween($dateColumn, [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw("{$bucket} as ym, COUNT(*) as aggregate")
            ->groupBy('ym')
            ->pluck('aggregate', 'ym');

        return self::align($ymList, $rows, 0);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $ymList
     * @return list<float>
     */
    public static function sums(
        Builder $query,
        string $dateColumn,
        string $sumColumn,
        Carbon $start,
        Carbon $end,
        array $ymList,
    ): array {
        $bucket = self::bucketExpression($dateColumn);
        $rows = (clone $query)
            ->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$bucket} as ym, COALESCE(SUM({$sumColumn}), 0) as aggregate")
            ->groupBy('ym')
            ->pluck('aggregate', 'ym');

        return array_map(
            fn (float $v): float => round($v, 2),
            self::align($ymList, $rows, 0.0),
        );
    }

    /**
     * Distinct patients per month for a doctor (visits).
     *
     * @param  list<string>  $ymList
     * @return list<int>
     */
    public static function distinctPatientsByVisitMonth(
        int $doctorId,
        Carbon $start,
        Carbon $end,
        array $ymList,
    ): array {
        $bucket = self::bucketExpression('visit_date');
        $rows = Visit::query()
            ->where('doctor_id', $doctorId)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$bucket} as ym, COUNT(DISTINCT patient_id) as aggregate")
            ->groupBy('ym')
            ->pluck('aggregate', 'ym');

        return self::align($ymList, $rows, 0);
    }

    private static function bucketExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * @param  list<string>  $ymList
     * @param  iterable<string, mixed>  $rows
     * @return list<int|float>
     */
    private static function align(array $ymList, iterable $rows, int|float $default): array
    {
        $map = collect($rows)->all();
        $out = [];
        foreach ($ymList as $ym) {
            $out[] = $map[$ym] ?? $default;
        }

        return $out;
    }
}
