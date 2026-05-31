<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Support\ClinicPermissions;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_PATIENTS);
    }

    public function create(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_PATIENTS);
    }

    /**
     * عرض صفحة المريض الطبية (الملف) — يتماشى مع صلاحية الملف المالي للطبيب.
     */
    public function view(User $user, Patient $patient): bool
    {
        return $this->viewProfile($user, $patient);
    }

    public function update(User $user, Patient $patient): bool
    {
        if (! $user->can(ClinicPermissions::MANAGE_PATIENTS)) {
            return false;
        }

        if ($user->hasRole('admin') || $user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasRole('doctor') && ! $user->hasRole('admin')) {
            return $this->patientRelatedToDoctor($user, $patient);
        }

        return true;
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $this->update($user, $patient);
    }

    /**
     * من يمكنه فتح ملف المريض المالي / الكشف (استثناء الطبيب يُقيَّد أدناه).
     */
    public function viewProfile(User $user, Patient $patient): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor') && ! $user->hasRole('admin')) {
            return $this->patientRelatedToDoctor($user, $patient);
        }

        return $user->can(ClinicPermissions::MANAGE_PATIENTS)
            || $user->can(ClinicPermissions::MANAGE_INVOICES)
            || $user->can(ClinicPermissions::VIEW_REPORTS);
    }

    /**
     * عرض بيانات زيارات/مواعيد تفصيلية (شكوى، تشخيص، ملاحظات).
     */
    public function viewPatientClinical(User $user, Patient $patient): bool
    {
        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor') && ! $user->hasRole('admin')) {
            return $this->patientRelatedToDoctor($user, $patient);
        }

        return $user->can(ClinicPermissions::MANAGE_VISITS)
            || $user->can(ClinicPermissions::MANAGE_APPOINTMENTS);
    }

    private function patientRelatedToDoctor(User $user, Patient $patient): bool
    {
        $doctor = $user->linkedDoctor();
        if (! $doctor) {
            return false;
        }

        $doctorId = (int) $doctor->id;

        return Visit::query()
            ->where('patient_id', $patient->id)
            ->where('doctor_id', $doctorId)
            ->exists()
            || Appointment::query()
                ->where('patient_id', $patient->id)
                ->where('doctor_id', $doctorId)
                ->exists();
    }
}
