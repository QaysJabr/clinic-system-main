<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use BelongsToClinic, HasFactory;

    /** Reception queue — لم يبدأ الطبيب بعد */
    public const STATUS_WAITING = 'waiting';

    /** الطبيب يعالج الزيارة */
    public const STATUS_IN_PROGRESS = 'in_progress';

    /** انتهاء الطبابة */
    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visit_date',
        'chief_complaint',
        'diagnosis',
        'treatment_plan',
        'procedures',
        'prescriptions',
        'notes',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Visit $visit): void {
            if ($visit->patient_id) {
                $patient = Patient::withoutGlobalScopes()->find($visit->patient_id);
                if ($patient) {
                    $visit->clinic_id = $patient->clinic_id;
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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * أحدث فاتورة مرتبطة بالزيارة (عند وجود أكثر من سجل).
     *
     * @return HasOne<Invoice, $this>
     */
    public function latestInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * @return HasMany<DoctorEarning, $this>
     */
    public function doctorEarnings(): HasMany
    {
        return $this->hasMany(DoctorEarning::class);
    }

    /**
     * إجراءات منظّمة (بالإضافة إلى الحقل النصي procedures).
     *
     * @return HasMany<VisitProcedure, $this>
     */
    public function structuredProcedures(): HasMany
    {
        return $this->hasMany(VisitProcedure::class);
    }

    /**
     * وصفات منظّمة (بالإضافة إلى الحقل النصي prescriptions).
     *
     * @return HasMany<VisitPrescription, $this>
     */
    public function structuredPrescriptions(): HasMany
    {
        return $this->hasMany(VisitPrescription::class);
    }

    public function soapNote(): HasOne
    {
        return $this->hasOne(VisitSoapNote::class);
    }

    /**
     * @return HasMany<PatientDiagnosis, $this>
     */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(PatientDiagnosis::class);
    }

    /**
     * Localized visit status label for UI, reports, and exports.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING => __('visits.status_waiting'),
            self::STATUS_IN_PROGRESS => __('visits.status_in_progress'),
            self::STATUS_COMPLETED => __('visits.status_completed'),
            self::STATUS_CANCELLED => __('visits.status_cancelled'),
            default => (string) $this->status,
        };
    }

    /**
     * Classes for status pill (Tailwind).
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800 dark:bg-emerald-950/45 dark:text-emerald-300',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-200',
            self::STATUS_WAITING => 'bg-amber-100 text-amber-800 dark:bg-amber-950/45 dark:text-amber-300',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-[#374151] dark:text-[#E5E7EB]',
        };
    }

    /**
     * Accent color for status indicators in lists and quick filters.
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => '#10B981',
            self::STATUS_IN_PROGRESS => '#3B82F6',
            self::STATUS_WAITING => '#F59E0B',
            self::STATUS_CANCELLED => '#EF4444',
            default => '#94A3B8',
        };
    }

    /** @return array<string, string> */
    public static function statusColors(): array
    {
        return [
            self::STATUS_WAITING => '#F59E0B',
            self::STATUS_IN_PROGRESS => '#3B82F6',
            self::STATUS_COMPLETED => '#10B981',
            self::STATUS_CANCELLED => '#EF4444',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
