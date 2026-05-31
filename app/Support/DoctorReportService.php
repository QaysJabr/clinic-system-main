<?php

namespace App\Support;

use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\Invoice;
use Carbon\CarbonInterface;

/**
 * تقرير طبيب: إيراد فواتيره، حصصه من doctor_earnings (مستحق/مدفوع للطبيب).
 */
final class DoctorReportService
{
    /**
     * @return array{
     *     invoice_count: int,
     *     accrual_revenue: float,
     *     doctor_share_total: float,
     *     earnings_pending: float,
     *     earnings_paid: float,
     *     earnings_pending_count: int,
     *     earnings_paid_count: int
     * }
     */
    public static function forDoctor(Doctor $doctor, ?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $invBase = Invoice::query()->where('doctor_id', $doctor->id)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $invoiceCount = (clone $invBase)->count();
        $accrualRevenue = (float) (clone $invBase)->sum('total');

        $earningBase = DoctorEarning::query()
            ->where('doctor_id', $doctor->id)
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q->when($from, fn ($q2) => $q2->whereDate('created_at', '>=', $from))
                    ->when($to, fn ($q2) => $q2->whereDate('created_at', '<=', $to));
            });

        $doctorShareTotal = (float) (clone $earningBase)->sum('earning_amount');

        $pending = (clone $earningBase)->where('status', DoctorEarning::STATUS_PENDING);
        $paid = (clone $earningBase)->where('status', DoctorEarning::STATUS_PAID);

        return [
            'invoice_count' => $invoiceCount,
            'accrual_revenue' => round($accrualRevenue, 2),
            'doctor_share_total' => round($doctorShareTotal, 2),
            'earnings_pending' => round((float) (clone $pending)->sum('earning_amount'), 2),
            'earnings_paid' => round((float) (clone $paid)->sum('earning_amount'), 2),
            'earnings_pending_count' => (int) (clone $pending)->count(),
            'earnings_paid_count' => (int) (clone $paid)->count(),
        ];
    }

    /**
     * فواتير الطبيب للعرض في الجدول (بدون N+1).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Invoice>
     */
    public static function invoicesForTable(Doctor $doctor, ?CarbonInterface $from = null, ?CarbonInterface $to = null)
    {
        return Invoice::query()
            ->where('doctor_id', $doctor->id)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->with([
                'patient:id,full_name',
                'doctorEarnings' => fn ($q) => $q->where('doctor_id', $doctor->id)->select(['id', 'invoice_id', 'doctor_id', 'earning_amount', 'status']),
            ])
            ->orderByDesc('created_at')
            ->get();
    }
}
