<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use App\Support\ClinicPermissions;

/**
 * Appointment access: permission gate + doctor may only touch own schedule (unless admin).
 */
class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_APPOINTMENTS);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if (! $user->can(ClinicPermissions::MANAGE_APPOINTMENTS)) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $appointment);
    }

    public function create(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_APPOINTMENTS);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if (! $user->can(ClinicPermissions::MANAGE_APPOINTMENTS)) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $appointment);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment);
    }

    private function sameDoctorOrElevated(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doctor = $user->linkedDoctor();

            return $doctor !== null && (int) $appointment->doctor_id === (int) $doctor->id;
        }

        return true;
    }
}
