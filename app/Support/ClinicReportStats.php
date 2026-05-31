<?php

namespace App\Support;

use App\Models\Invoice;

/**
 * تجميعات إحصائيات الفواتير لتقليل عدد الاستعلامات في لوحة التحكم والتقارير.
 */
final class ClinicReportStats
{
    /**
     * @return array{unpaid: int, partial: int, paid: int}
     */
    public static function invoiceCountsByStatus(?int $doctorId = null): array
    {
        $rows = Invoice::query()
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'unpaid' => (int) ($rows['unpaid'] ?? 0),
            'partial' => (int) ($rows['partial'] ?? 0),
            'paid' => (int) ($rows['paid'] ?? 0),
        ];
    }

    /**
     * @return array{unpaid_amount: float, partial_amount: float, paid_amount: float}
     */
    public static function invoiceAmountsByStatus(): array
    {
        $row = Invoice::query()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN total ELSE 0 END), 0) as unpaid_amount,'.
                'COALESCE(SUM(CASE WHEN status = ? THEN (total - paid) ELSE 0 END), 0) as partial_amount,'.
                'COALESCE(SUM(CASE WHEN status = ? THEN total ELSE 0 END), 0) as paid_amount',
                ['unpaid', 'partial', 'paid']
            )
            ->first();

        return [
            'unpaid_amount' => (float) ($row->unpaid_amount ?? 0),
            'partial_amount' => (float) ($row->partial_amount ?? 0),
            'paid_amount' => (float) ($row->paid_amount ?? 0),
        ];
    }
}
