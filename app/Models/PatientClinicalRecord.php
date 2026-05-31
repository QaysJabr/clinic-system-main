<?php

namespace App\Models;

use App\Enums\ClinicalRecordType;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientClinicalRecord extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'type',
        'title',
        'details',
        'severity',
        'is_active',
        'recorded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function typeEnum(): ClinicalRecordType
    {
        return ClinicalRecordType::tryFrom($this->type) ?? ClinicalRecordType::RiskFlag;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
