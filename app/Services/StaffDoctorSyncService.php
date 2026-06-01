<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Staff;
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
        return $request->only(self::doctorFieldKeys());
    }

    /**
     * Create or update the doctors row linked to this staff member.
     */
    public function syncDoctorForStaff(Staff $staff, array $doctorFields): Doctor
    {
        $doctor = Doctor::query()->firstOrNew(['staff_id' => $staff->id]);

        $doctor->fill([
            'clinic_id' => $staff->clinic_id,
            'staff_id' => $staff->id,
            'full_name' => $staff->full_name,
            'phone' => $staff->phone,
            'email' => $staff->email,
            'status' => $staff->status,
            'specialty' => $doctorFields['specialty'] ?? null,
            'license_number' => $doctorFields['license_number'] ?? null,
            'room_number' => $doctorFields['room_number'] ?? null,
            'notes' => $doctorFields['notes'] ?? null,
        ]);

        $doctor->save();

        return $doctor;
    }

    /**
     * Remove linked doctor when staff is no longer a doctor.
     */
    public function removeDoctorForStaff(Staff $staff): void
    {
        Doctor::query()->where('staff_id', $staff->id)->delete();
    }
}
