<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use BelongsToClinic, HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'staff_id',
        'full_name',
        'specialty',
        'phone',
        'email',
        'license_number',
        'room_number',
        'status',
        'notes',
    ];

    protected static function booted(): void
    {
        static::saving(function (Doctor $doctor): void {
            if ($doctor->staff_id) {
                $staff = Staff::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($doctor->staff_id);
                if ($staff && $staff->clinic_id) {
                    $doctor->clinic_id = $staff->clinic_id;
                }
            }
        });
    }

    /**
     * ربط الطبيب بسجل موظف (للنسبة المئوية وأرباح الفواتير).
     *
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * @return HasMany<DoctorEarning, $this>
     */
    public function earnings(): HasMany
    {
        return $this->hasMany(DoctorEarning::class, 'doctor_id');
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'doctor_id');
    }
}
