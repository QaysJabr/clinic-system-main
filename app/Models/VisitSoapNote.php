<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitSoapNote extends Model
{
    protected $fillable = [
        'visit_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
