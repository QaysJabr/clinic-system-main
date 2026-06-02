<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class UserStaffLinkService
{
    public function __construct(
        private readonly StaffDoctorSyncService $staffDoctorSync,
    ) {}

    /**
     * Map staff.role_type to Spatie web role name.
     */
    public static function roleForStaff(Staff $staff): string
    {
        return match ($staff->role_type) {
            'doctor' => 'doctor',
            'receptionist' => 'receptionist',
            'accountant' => 'accountant',
            default => 'receptionist',
        };
    }

    /**
     * Create a login user from an existing staff row and link staff.user_id.
     */
    public function createUserForStaff(Staff $staff, string $password, ?string $roleOverride = null): User
    {
        if ($staff->user_id !== null) {
            throw ValidationException::withMessages([
                'staff_id' => __('settings.users_error_staff_already_linked'),
            ]);
        }

        $email = filled($staff->email) ? strtolower(trim((string) $staff->email)) : null;

        if ($email === null) {
            throw ValidationException::withMessages([
                'staff_id' => __('settings.users_error_staff_needs_email'),
            ]);
        }

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'staff_id' => __('settings.users_error_staff_email_taken'),
            ]);
        }

        $role = $roleOverride ?? self::roleForStaff($staff);

        $user = User::query()->create([
            'name' => $staff->full_name,
            'email' => $email,
            'password' => $password,
            'clinic_id' => $staff->clinic_id,
        ]);

        $user->syncRoles([$role]);

        $staff->update(['user_id' => $user->id]);

        if ($staff->role_type === 'doctor') {
            $this->staffDoctorSync->ensureDoctorUserRole($staff);
        }

        return $user;
    }
}
