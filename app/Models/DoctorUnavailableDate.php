<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorUnavailableDate extends Model
{
    use BelongsToClinic;

    public const TYPE_VACATION = 'vacation';

    public const TYPE_BLOCKED = 'blocked';

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'unavailable_date',
        'all_day',
        'start_time',
        'end_time',
        'type',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'unavailable_date' => 'date',
            'all_day' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
