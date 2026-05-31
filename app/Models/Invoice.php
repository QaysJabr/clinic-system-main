<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'visit_id',
        'doctor_id',
        'invoice_number',
        'total',
        'paid',
        'status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * doctor_id مرآة لـ visits.doctor_id عند وجود visit_id؛ لا يُعرَّف يدوياً.
     * بدون زيارة لا يُشتق طبيب معالج من النظام الحالي (لا أرباح نسبة من مسار الزيارة).
     */
    protected static function booted(): void
    {
        static::saving(function (Invoice $invoice): void {
            if ($invoice->visit_id) {
                $visit = Visit::query()
                    ->select(['id', 'doctor_id', 'clinic_id'])
                    ->find($invoice->visit_id);
                $invoice->doctor_id = $visit?->doctor_id;
                if ($visit && ! $invoice->clinic_id) {
                    $invoice->clinic_id = $visit->clinic_id;
                }
            } else {
                $invoice->doctor_id = null;
            }

            if (! $invoice->clinic_id && $invoice->patient_id) {
                $patient = Patient::withoutGlobalScopes()
                    ->select(['id', 'clinic_id'])
                    ->find($invoice->patient_id);
                if ($patient) {
                    $invoice->clinic_id = $patient->clinic_id;
                }
            }
        });
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * الطبيب المعالج المشتق من الزيارة (نسخ على الفاتورة للاستعلام والتقارير).
     *
     * @return BelongsTo<Doctor, $this>
     */
    public function treatingDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<DoctorEarning, $this>
     */
    public function doctorEarnings(): HasMany
    {
        return $this->hasMany(DoctorEarning::class);
    }

    public function balanceRemaining(): float
    {
        return round(max(0, (float) $this->total - (float) $this->paid), 2);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => __('common.paid'),
            'partial' => __('common.partial'),
            'unpaid' => __('common.unpaid'),
            default => (string) $this->status,
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300',
            'partial' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/45 dark:text-amber-300',
            default => 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'paid' => '#10B981',
            'partial' => '#F59E0B',
            default => '#EF4444',
        };
    }

    /** @return array<string, string> */
    public static function statusColors(): array
    {
        return [
            'unpaid' => '#EF4444',
            'partial' => '#F59E0B',
            'paid' => '#10B981',
        ];
    }
}
