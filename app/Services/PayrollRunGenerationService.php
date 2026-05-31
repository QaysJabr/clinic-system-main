<?php

namespace App\Services;

use App\Enums\StaffCompensationModel;
use App\Models\PayrollRun;
use App\Models\StaffCompensationProfile;
use App\Models\StaffPayment;
use App\Support\StaffPayrollCalculator;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Creates staff_payments for a payroll run: matches active staff whose
 * compensation profile payment_cycle equals the run period_type (weekly/monthly).
 */
final class PayrollRunGenerationService
{
    public function generate(PayrollRun $run, ?int $createdByUserId): PayrollRunGenerationResult
    {
        return DB::transaction(function () use ($run, $createdByUserId) {
            /** @var PayrollRun $run */
            $run = PayrollRun::query()->lockForUpdate()->findOrFail($run->id);

            $created = 0;
            $skippedExisting = 0;
            $skippedInactiveStaff = 0;
            $skippedBeforeProfileStart = 0;
            $skippedIncompleteProfile = 0;

            $profiles = StaffCompensationProfile::query()
                ->where('status', 'active')
                ->where('payment_cycle', $run->period_type->value)
                ->with('staff')
                ->orderBy('staff_id')
                ->get();

            foreach ($profiles as $profile) {
                $staff = $profile->staff;
                if (! $staff || $staff->status !== 'active') {
                    $skippedInactiveStaff++;

                    continue;
                }

                if ($profile->start_date && $run->period_end->lt($profile->start_date)) {
                    $skippedBeforeProfileStart++;

                    continue;
                }

                $duplicate = StaffPayment::query()
                    ->where('staff_id', $profile->staff_id)
                    ->whereDate('period_start', $run->period_start)
                    ->whereDate('period_end', $run->period_end)
                    ->exists();

                if ($duplicate) {
                    $skippedExisting++;

                    continue;
                }

                [$calcStart, $calcEnd] = $this->effectiveCalculationWindow($profile, $run);

                if ($calcStart->gt($run->period_end)) {
                    $skippedIncompleteProfile++;

                    continue;
                }

                $baseAmount = $this->resolveBaseAmount($profile, $calcStart, $calcEnd);
                if ($baseAmount === null) {
                    $skippedIncompleteProfile++;

                    continue;
                }

                $bonus = 0.0;
                $deduction = 0.0;
                $paid = 0.0;
                $totals = StaffPayment::computeTotals($baseAmount, $bonus, $deduction, $paid);

                $daysWorked = null;
                if ($profile->compensation_type === StaffCompensationModel::Daily) {
                    $daysWorked = (int) $calcStart->diffInDays($calcEnd) + 1;
                }

                $notes = null;
                if ($profile->compensation_type === StaffCompensationModel::Percentage) {
                    $notes = 'تعويض بالنسبة — راجع المبلغ الأساسي أو اربطه بمصدر الإيرادات.';
                }

                StaffPayment::query()->create([
                    'payroll_run_id' => $run->id,
                    'staff_id' => $profile->staff_id,
                    'compensation_profile_id' => $profile->id,
                    'period_type' => $run->period_type,
                    'period_start' => $run->period_start,
                    'period_end' => $run->period_end,
                    'base_amount' => number_format($baseAmount, 2, '.', ''),
                    'bonus' => '0.00',
                    'deduction' => '0.00',
                    'total_due' => number_format($totals['total_due'], 2, '.', ''),
                    'paid_amount' => '0.00',
                    'remaining_amount' => number_format($totals['remaining_amount'], 2, '.', ''),
                    'days_worked' => $daysWorked,
                    'payment_date' => null,
                    'payment_method' => null,
                    'source_type' => 'payroll_run',
                    'notes' => $notes,
                    'created_by' => $createdByUserId,
                ]);

                $created++;
            }

            $run->update([
                'status' => 'generated',
                'generated_at' => now(),
                'generated_by' => $createdByUserId,
            ]);

            return new PayrollRunGenerationResult(
                $created,
                $skippedExisting,
                $skippedInactiveStaff,
                $skippedBeforeProfileStart,
                $skippedIncompleteProfile,
            );
        });
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function effectiveCalculationWindow(StaffCompensationProfile $profile, PayrollRun $run): array
    {
        $calcStart = $run->period_start->copy()->startOfDay();
        if ($profile->start_date !== null) {
            $sd = $profile->start_date->copy()->startOfDay();
            if ($calcStart->lt($sd)) {
                $calcStart = $sd;
            }
        }

        $calcEnd = $run->period_end->copy()->startOfDay();

        return [$calcStart, $calcEnd];
    }

    private function resolveBaseAmount(
        StaffCompensationProfile $profile,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd
    ): ?float {
        if ($profile->compensation_type === StaffCompensationModel::Percentage) {
            return 0.0;
        }

        $suggested = StaffPayrollCalculator::suggestedAmount($profile, $periodStart, $periodEnd);
        if ($suggested === null || $suggested === '') {
            return null;
        }

        return (float) $suggested;
    }
}
