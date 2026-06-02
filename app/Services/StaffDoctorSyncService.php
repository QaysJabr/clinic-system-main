<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

final class StaffDoctorSyncService
{
    /**
     * @return list<string>
     */
    public static function doctorFieldKeys(): array
    {
        return ['specialty', 'license_number', 'room_number', 'notes'];
    }

    /**
     * @return array<string, mixed>
     */
    public function doctorFieldsFromRequest(Request $request): array
    {
        return $this->normalizeDoctorFields($request->only(self::doctorFieldKeys()));
    }

    /**
     * Create or update the doctors row linked to this staff member.
     */
    public function syncDoctorForStaff(Staff $staff, array $doctorFields): Doctor
    {
        $doctor = $this->resolveDoctorForStaff($staff);
        $doctorFields = $this->normalizeDoctorFields($doctorFields);

        $doctor->fill([
            'clinic_id' => $staff->clinic_id,
            'staff_id' => $staff->id,
            'full_name' => $staff->full_name,
            'phone' => filled($staff->phone) ? $staff->phone : null,
            'email' => filled($staff->email) ? $staff->email : null,
            'status' => $staff->status,
            'specialty' => $doctorFields['specialty'] ?? null,
            'license_number' => $doctorFields['license_number'] ?? null,
            'room_number' => $doctorFields['room_number'] ?? null,
            'notes' => $doctorFields['notes'] ?? null,
        ]);

        $doctor->save();

        $this->ensureDoctorUserRole($staff);

        return $doctor;
    }

    /**
     * Remove linked doctor when staff is no longer a doctor.
     */
    public function removeDoctorForStaff(Staff $staff): void
    {
        Doctor::query()
            ->withoutGlobalScopes()
            ->where('staff_id', $staff->id)
            ->delete();
    }

    /**
     * Ensure linked user has Spatie role `doctor` when staff is a doctor with login.
     */
    public function ensureDoctorUserRole(Staff $staff): void
    {
        if ($staff->role_type !== 'doctor' || ! $staff->user_id) {
            return;
        }

        $user = User::query()->withoutGlobalScopes()->find($staff->user_id);

        if ($user !== null && ! $user->hasRole('doctor')) {
            $user->assignRole('doctor');
        }
    }

    /**
     * Find existing doctor row for staff (tenant-safe) or prepare a new model.
     */
    public function resolveDoctorForStaff(Staff $staff): Doctor
    {
        $query = Doctor::query()
            ->withoutGlobalScopes()
            ->where('staff_id', $staff->id);

        if ($staff->clinic_id !== null) {
            $query->where('clinic_id', $staff->clinic_id);
        }

        $existing = $query->first();

        if ($existing !== null) {
            return $existing;
        }

        $doctor = new Doctor;
        $doctor->staff_id = $staff->id;
        $doctor->clinic_id = $staff->clinic_id;

        return $doctor;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function normalizeDoctorFields(array $fields): array
    {
        $normalized = [];

        foreach (self::doctorFieldKeys() as $key) {
            $value = $fields[$key] ?? null;
            $normalized[$key] = filled($value) ? $value : null;
        }

        return $normalized;
    }
}
