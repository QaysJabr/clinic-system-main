<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorScheduleBreak extends Model
{
    protected $fillable = [
        'doctor_schedule_id',
        'start_time',
        'end_time',
        'label',
    ];

    /**
     * @return BelongsTo<DoctorSchedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id');
    }
}
