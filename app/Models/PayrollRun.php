<?php

namespace App\Models;

use App\Enums\PaymentCycle;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'period_type',
        'period_start',
        'period_end',
        'status',
        'generated_at',
        'generated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_type' => PaymentCycle::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * @return HasMany<StaffPayment, $this>
     */
    public function staffPayments(): HasMany
    {
        return $this->hasMany(StaffPayment::class);
    }

    public function periodLabel(): string
    {
        $t = $this->period_type->label();

        return $t.' — '.$this->period_start?->format('d/m/Y').' → '.$this->period_end?->format('d/m/Y');
    }

    /**
     * @deprecated Use {@see periodLabel()}
     */
    public function periodLabelAr(): string
    {
        return $this->periodLabel();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => __('payroll.run_status_draft'),
            'generated' => __('payroll.run_status_generated'),
            default => $this->status,
        };
    }

    /**
     * @deprecated Use {@see statusLabel()}
     */
    public function statusLabelAr(): string
    {
        return $this->statusLabel();
    }
}
