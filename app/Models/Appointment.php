<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Concerns\BelongsToClinic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use BelongsToClinic, HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'duration_minutes',
        'checked_in_at',
        'visit_id',
        'reason',
        'notes',
        'booking_source',
        'public_booking_token',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'checked_in_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment): void {
            if ($appointment->patient_id) {
                $patient = Patient::withoutGlobalScopes()->find($appointment->patient_id);
                if ($patient) {
                    $appointment->clinic_id = $patient->clinic_id;
                }
            }

            if ($appointment->start_time && ! $appointment->end_time) {
                $mins = $appointment->duration_minutes
                    ?? config('scheduling.default_slot_minutes', 15);
                $start = Carbon::parse('2000-01-01 '.$appointment->start_time);
                $appointment->end_time = $start->copy()->addMinutes($mins)->format('H:i');
            }

            if ($appointment->start_time && $appointment->end_time && ! $appointment->duration_minutes) {
                $start = Carbon::parse('2000-01-01 '.$appointment->start_time);
                $end = Carbon::parse('2000-01-01 '.$appointment->end_time);
                $appointment->duration_minutes = max(1, (int) $start->diffInMinutes($end));
            }
        });
    }

    public function statusEnum(): AppointmentStatus
    {
        return AppointmentStatus::tryFrom($this->status) ?? AppointmentStatus::Scheduled;
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

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * @return HasMany<AppointmentReminder, $this>
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }
}
