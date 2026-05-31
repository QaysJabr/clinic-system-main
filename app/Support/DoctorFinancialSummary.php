<?php

namespace App\Support;

use App\Models\DoctorEarning;

/**
 * Aggregates doctor settlement lines (doctor_earnings): pending vs paid-to-doctor.
 * Patient invoice payment ≠ doctor payout; earnings stay pending until marked paid in UI.
 */
final class DoctorFinancialSummary
{
    /**
     * @return array{pending_total: float, paid_total: float, pending_count: int, paid_count: int}
     */
    public static function forDoctor(int $doctorId): array
    {
        $pendingTotal = (float) DoctorEarning::query()
            ->where('doctor_id', $doctorId)
            ->where('status', DoctorEarning::STATUS_PENDING)
            ->sum('earning_amount');

        $paidTotal = (float) DoctorEarning::query()
            ->where('doctor_id', $doctorId)
            ->where('status', DoctorEarning::STATUS_PAID)
            ->sum('earning_amount');

        $pendingCount = DoctorEarning::query()
            ->where('doctor_id', $doctorId)
            ->where('status', DoctorEarning::STATUS_PENDING)
            ->count();

        $paidCount = DoctorEarning::query()
            ->where('doctor_id', $doctorId)
            ->where('status', DoctorEarning::STATUS_PAID)
            ->count();

        return [
            'pending_total' => round($pendingTotal, 2),
            'paid_total' => round($paidTotal, 2),
            'pending_count' => $pendingCount,
            'paid_count' => $paidCount,
        ];
    }
}
