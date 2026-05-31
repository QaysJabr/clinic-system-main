<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    use BelongsToClinic;

    protected $table = 'staff';

    /**
     * @var list<string>
     */
    public const ROLE_TYPES = [
        'doctor',
        'receptionist',
        'accountant',
        'nurse',
        'worker',
        'cleaner',
        'assistant',
        'other',
    ];

    protected $fillable = [
        'clinic_id',
        'user_id',
        'full_name',
        'role_type',
        'phone',
        'email',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    public static function roleTypeOptions(): array
    {
        return [
            'doctor' => __('payroll.role_doctor'),
            'receptionist' => __('payroll.role_receptionist'),
            'accountant' => __('payroll.role_accountant'),
            'nurse' => __('payroll.role_nurse'),
            'worker' => __('payroll.role_worker'),
            'cleaner' => __('payroll.role_cleaner'),
            'assistant' => __('payroll.role_assistant'),
            'other' => __('payroll.role_other'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function roleTypeOptionsAr(): array
    {
        return self::roleTypeOptions();
    }

    protected static function booted(): void
    {
        static::saving(function (Staff $staff): void {
            if ($staff->user_id) {
                $user = User::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($staff->user_id);
                if ($user && $user->clinic_id) {
                    $staff->clinic_id = $user->clinic_id;
                }
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return HasOne<StaffCompensationProfile, $this>
     */
    public function compensationProfile(): HasOne
    {
        return $this->hasOne(StaffCompensationProfile::class);
    }

    public function roleTypeLabel(): string
    {
        return match ($this->role_type) {
            'doctor' => __('payroll.role_doctor'),
            'receptionist' => __('payroll.role_receptionist'),
            'accountant' => __('payroll.role_accountant'),
            'nurse' => __('payroll.role_nurse'),
            'worker' => __('payroll.role_worker'),
            'cleaner' => __('payroll.role_cleaner'),
            'assistant' => __('payroll.role_assistant'),
            'other' => __('payroll.role_other'),
            default => $this->role_type ? (string) $this->role_type : __('common.em_dash'),
        };
    }

    public function roleTypeLabelAr(): string
    {
        return $this->roleTypeLabel();
    }

    public function statusBadgeClasses(): string
    {
        return $this->status === 'active'
            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300'
            : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    }

    public function statusColor(): string
    {
        return $this->status === 'active' ? '#10B981' : '#94A3B8';
    }

    /**
     * @return HasMany<StaffPayment, $this>
     */
    public function staffPayments(): HasMany
    {
        return $this->hasMany(StaffPayment::class);
    }

    /**
     * أرباح الأطباء عبر سجل الطبيب (doctors.staff_id → doctors.id → doctor_earnings.doctor_id).
     *
     * @return HasManyThrough<DoctorEarning, Doctor, $this>
     */
    public function doctorEarnings(): HasManyThrough
    {
        return $this->hasManyThrough(
            DoctorEarning::class,
            Doctor::class,
            'staff_id',
            'doctor_id',
            'id',
            'id'
        );
    }
}
