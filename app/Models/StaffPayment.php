<?php

namespace App\Models;

use App\Enums\PaymentCycle;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'payroll_run_id',
        'staff_id',
        'compensation_profile_id',
        'period_type',
        'period_start',
        'period_end',
        'base_amount',
        'bonus',
        'deduction',
        'total_due',
        'paid_amount',
        'remaining_amount',
        'days_worked',
        'payment_date',
        'payment_method',
        'source_type',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_type' => PaymentCycle::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'days_worked' => 'integer',
            'base_amount' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'total_due' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (StaffPayment $staffPayment): void {
            if ($staffPayment->staff_id) {
                $staff = Staff::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($staffPayment->staff_id);
                if ($staff && $staff->clinic_id) {
                    $staffPayment->clinic_id = $staff->clinic_id;
                }
            }
        });
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<PayrollRun, $this>
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * @return BelongsTo<StaffCompensationProfile, $this>
     */
    public function compensationProfile(): BelongsTo
    {
        return $this->belongsTo(StaffCompensationProfile::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function periodLabel(): string
    {
        $t = $this->period_type->label();
        $start = $this->period_start?->format('d/m/Y') ?? '';
        $end = $this->period_end?->format('d/m/Y') ?? '';

        return trim($t.' '.__('payroll.period_range_separator').' '.$start.' '.__('payroll.period_date_arrow').' '.$end);
    }

    public function periodLabelAr(): string
    {
        return $this->periodLabel();
    }

    /**
     * حالة العرض: مكتمل / جزئي / غير مدفوع.
     */
    public function settlementStatus(): string
    {
        if ((float) $this->remaining_amount <= 0.00001) {
            return 'completed';
        }
        if ((float) $this->paid_amount <= 0.00001) {
            return 'unpaid';
        }

        return 'partial';
    }

    public function displayStatus(): string
    {
        return match ($this->settlementStatus()) {
            'completed' => __('payroll.status_completed'),
            'unpaid' => __('payroll.status_unpaid'),
            default => __('payroll.status_partial'),
        };
    }

    public function displayStatusAr(): string
    {
        return $this->displayStatus();
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->settlementStatus()) {
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
            'partial' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-300',
            default => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300',
        };
    }

    public function statusDotColor(): string
    {
        return match ($this->settlementStatus()) {
            'completed' => 'bg-emerald-500',
            'partial' => 'bg-amber-500',
            default => 'bg-rose-500',
        };
    }

    /**
     * @return array{total_due: float, remaining_amount: float}
     */
    public static function computeTotals(float $base, float $bonus, float $deduction, float $paid): array
    {
        $totalDue = round($base + $bonus - $deduction, 2);
        $remaining = round($totalDue - $paid, 2);

        return [
            'total_due' => $totalDue,
            'remaining_amount' => $remaining,
        ];
    }
}
