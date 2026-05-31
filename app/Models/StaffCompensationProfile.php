<?php

namespace App\Models;

use App\Enums\PaymentCycle;
use App\Enums\StaffCompensationModel;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffCompensationProfile extends Model
{
    use BelongsToClinic;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'clinic_id',
        'staff_id',
        'compensation_type',
        'payment_cycle',
        'base_salary',
        'percentage_rate',
        'daily_wage',
        'calculation_basis',
        'start_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'compensation_type' => StaffCompensationModel::class,
            'payment_cycle' => PaymentCycle::class,
            'base_salary' => 'decimal:2',
            'percentage_rate' => 'decimal:2',
            'daily_wage' => 'decimal:2',
            'start_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (StaffCompensationProfile $profile): void {
            if ($profile->staff_id) {
                $staff = Staff::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($profile->staff_id);
                if ($staff && $staff->clinic_id) {
                    $profile->clinic_id = $staff->clinic_id;
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
     * Badge: compensation type + payment cycle (fixed / percentage / daily + weekly / monthly).
     */
    public function compactBadge(): string
    {
        $type = match ($this->compensation_type) {
            StaffCompensationModel::Percentage => __('staff.comp_percentage'),
            StaffCompensationModel::Daily => __('staff.comp_daily'),
            StaffCompensationModel::Fixed => __('staff.comp_fixed'),
        };

        return $type.' · '.$this->payment_cycle->label();
    }

    public function compactBadgeAr(): string
    {
        return $this->compactBadge();
    }

    public function summaryLabel(): string
    {
        $dash = __('common.em_dash');
        $cycle = $this->payment_cycle->label();

        return match ($this->compensation_type) {
            StaffCompensationModel::Fixed => __('staff.summary_fixed', [
                'amount' => $this->base_salary !== null ? (string) $this->base_salary : $dash,
                'cycle' => $cycle,
            ]),
            StaffCompensationModel::Percentage => __('staff.summary_percentage', [
                'rate' => $this->percentage_rate !== null ? (string) $this->percentage_rate : $dash,
                'basis' => self::basisLabel((string) ($this->calculation_basis ?? '')),
                'cycle' => $cycle,
            ]),
            StaffCompensationModel::Daily => __('staff.summary_daily', [
                'amount' => $this->daily_wage !== null ? (string) $this->daily_wage : $dash,
                'cycle' => $cycle,
            ]),
        };
    }

    public function summaryLabelAr(): string
    {
        return $this->summaryLabel();
    }

    private static function basisLabel(string $b): string
    {
        return match ($b) {
            'invoice_paid_total' => __('staff.basis_invoice_paid_total'),
            'gross_revenue' => __('staff.basis_gross_revenue'),
            default => $b !== '' ? $b : __('common.em_dash'),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => __('staff.status_active'),
            self::STATUS_INACTIVE => __('staff.status_inactive'),
            default => $this->status,
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
            self::STATUS_INACTIVE => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
    }
}
