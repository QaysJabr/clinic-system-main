<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitPrescription extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'visit_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration',
        'notes',
    ];

    protected static function booted(): void
    {
        static::saving(function (VisitPrescription $prescription): void {
            if ($prescription->visit_id) {
                $visit = Visit::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($prescription->visit_id);
                if ($visit && $visit->clinic_id) {
                    $prescription->clinic_id = $visit->clinic_id;
                }
            }
        });
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
