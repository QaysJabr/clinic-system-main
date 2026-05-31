<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitProcedure extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'visit_id',
        'name',
        'notes',
    ];

    protected static function booted(): void
    {
        static::saving(function (VisitProcedure $procedure): void {
            if ($procedure->visit_id) {
                $visit = Visit::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($procedure->visit_id);
                if ($visit && $visit->clinic_id) {
                    $procedure->clinic_id = $visit->clinic_id;
                }
            }
        });
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
