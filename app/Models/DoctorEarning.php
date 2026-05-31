<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorEarning extends Model
{
    use BelongsToClinic;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'invoice_id',
        'visit_id',
        'total_amount',
        'percentage_rate',
        'earning_amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'percentage_rate' => 'decimal:2',
            'earning_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * الطبيب (جدول doctors) صاحب الأرباح.
     *
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Visit, $this>
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => __('doctors.earnings_status_paid_option'),
            self::STATUS_PENDING => __('doctors.earnings_status_pending'),
            default => $this->status,
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
            default => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-300',
        };
    }

    public function statusDotColor(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'bg-emerald-500',
            default => 'bg-amber-500',
        };
    }
}
