<?php

namespace App\Support;

use App\Models\DoctorEarning;
use Illuminate\Support\Carbon;

/**
 * تجميعات أرباح الأطباء (نسبة) للوحة التحكم والتقارير المختصرة.
 */
final class ClinicDoctorEarningStats
{
    /** تجميع العيادة بالكامل. مرِّر `$doctorId` لتقييد سجلات طبيب واحد (عرض شخصي للطبيب). */
    public static function totalEarnings(?int $doctorId = null): float
    {
        $q = DoctorEarning::query();
        if ($doctorId !== null) {
            $q->where('doctor_id', $doctorId);
        }

        return round((float) ($q->sum('earning_amount') ?? 0), 2);
    }

    public static function pendingTotal(?int $doctorId = null): float
    {
        $q = DoctorEarning::query()
            ->where('status', DoctorEarning::STATUS_PENDING);
        if ($doctorId !== null) {
            $q->where('doctor_id', $doctorId);
        }

        return round((float) ($q->sum('earning_amount') ?? 0), 2);
    }

    public static function paidTotal(?int $doctorId = null): float
    {
        $q = DoctorEarning::query()
            ->where('status', DoctorEarning::STATUS_PAID);
        if ($doctorId !== null) {
            $q->where('doctor_id', $doctorId);
        }

        return round((float) ($q->sum('earning_amount') ?? 0), 2);
    }

    /**
     * ملخص شهري حسب تاريخ إنشاء سجل الأرباح (وقت الاعتراف المحاسبي).
     *
     * @return list<array{ym: string, label: string, pending: float, paid: float, total: float}>
     */
    public static function monthlyTrend(int $months = 12, ?Carbon $at = null, ?int $doctorId = null): array
    {
        $at = $at ?? now();
        $months = max(1, $months);
        $start = $at->copy()->subMonths($months - 1)->startOfMonth();
        $end = $at->copy()->endOfMonth();

        $keys = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $ym = $m->format('Y-m');
            $keys[$ym] = [
                'ym' => $ym,
                'label' => $m->copy()->locale('ar')->translatedFormat('F Y'),
                'pending' => 0.0,
                'paid' => 0.0,
                'total' => 0.0,
            ];
        }

        $earningQuery = DoctorEarning::query()
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId));

        $earningQuery
            ->select(['id', 'created_at', 'status', 'earning_amount'])
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$keys): void {
                foreach ($chunk as $row) {
                    $ym = $row->created_at->format('Y-m');
                    if (! isset($keys[$ym])) {
                        continue;
                    }
                    $amt = (float) $row->earning_amount;
                    $keys[$ym]['total'] += $amt;
                    if ($row->status === DoctorEarning::STATUS_PENDING) {
                        $keys[$ym]['pending'] += $amt;
                    } elseif ($row->status === DoctorEarning::STATUS_PAID) {
                        $keys[$ym]['paid'] += $amt;
                    }
                }
            });

        $out = [];
        foreach ($keys as $row) {
            $row['pending'] = round($row['pending'], 2);
            $row['paid'] = round($row['paid'], 2);
            $row['total'] = round($row['total'], 2);
            $out[] = $row;
        }

        return array_reverse($out);
    }
}
