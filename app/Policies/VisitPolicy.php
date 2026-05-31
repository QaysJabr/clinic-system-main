<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

/**
 * مستخدم الدور طبيب يرى ويحرّر زياراته فقط عند وجود ربط staff ↔ doctor.
 */
class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage visits');
    }

    public function view(User $user, Visit $visit): bool
    {
        if (! $user->can('manage visits')) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $visit);
    }

    public function create(User $user): bool
    {
        return $user->can('manage visits');
    }

    public function update(User $user, Visit $visit): bool
    {
        if (! $user->can('manage visits')) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $visit);
    }

    public function delete(User $user, Visit $visit): bool
    {
        if (! $user->can('manage visits')) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $visit);
    }

    private function sameDoctorOrElevated(User $user, Visit $visit): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            $doctor = $user->linkedDoctor();

            return $doctor !== null && (int) $visit->doctor_id === (int) $doctor->id;
        }

        return true;
    }
}
