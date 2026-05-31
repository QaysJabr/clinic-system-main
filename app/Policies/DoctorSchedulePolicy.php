<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;

class DoctorSchedulePolicy
{
    public function viewAny(User $user, ?Doctor $doctor = null): bool
    {
        return $user->can('manage doctors') || $user->can('manage appointments');
    }

    public function manage(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('manage doctors') || $user->can('manage appointments');
    }
}
