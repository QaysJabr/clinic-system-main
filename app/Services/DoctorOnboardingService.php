<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\TenantValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DoctorOnboardingService
{
    public function __construct(
        private readonly StaffDoctorSyncService $staffDoctorSync,
    ) {}

    /**
     * @param  array{
     *   account_mode: string,
     *   full_name: string,
     *   email?: string,
     *   password?: string,
     *   user_id?: int|null,
     *   phone?: string|null,
     *   status: string,
     *   specialty?: string|null,
     *   license_number?: string|null,
     *   room_number?: string|null,
     *   notes?: string|null,
     * }  $data
     * @return array{user: User, staff: Staff, doctor: Doctor}
     */
    public function create(array $data, User $actor): array
    {
        $clinicId = $actor->clinic_id ?? config('tenancy.default_clinic_id');
        if ($clinicId === null) {
            throw ValidationException::withMessages([
                'full_name' => __('doctor_onboarding.error_no_clinic'),
            ]);
        }

        return DB::transaction(function () use ($data, $actor, $clinicId): array {
            $user = $this->resolveUser($data, (int) $clinicId, $actor);
            $email = strtolower(trim((string) $user->email));

            if (Staff::query()->where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => __('doctor_onboarding.error_user_already_staff'),
                ]);
            }

            $staff = Staff::query()->create([
                'clinic_id' => $clinicId,
                'full_name' => $data['full_name'],
                'role_type' => 'doctor',
                'phone' => $data['phone'] ?? null,
                'email' => $email,
                'user_id' => $user->id,
                'status' => $data['status'],
            ]);

            $doctor = $this->staffDoctorSync->syncDoctorForStaff($staff, [
                'specialty' => $data['specialty'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'room_number' => $data['room_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            AuditLogger::log(
                'create',
                'doctor_onboarding',
                $doctor->id,
                __('doctor_onboarding.audit_created', ['name' => $doctor->full_name, 'email' => $email]),
                null,
                [
                    'user_id' => $user->id,
                    'staff_id' => $staff->id,
                    'doctor_id' => $doctor->id,
                ],
            );

            return compact('user', 'staff', 'doctor');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveUser(array $data, int $clinicId, User $actor): User
    {
        if (($data['account_mode'] ?? '') === 'existing') {
            $userId = (int) ($data['user_id'] ?? 0);
            $user = User::query()->where('id', $userId)->where('clinic_id', $clinicId)->first();
            if ($user === null) {
                throw ValidationException::withMessages([
                    'user_id' => __('doctor_onboarding.error_invalid_user'),
                ]);
            }
            if (! $user->hasRole('doctor')) {
                $user->assignRole('doctor');
            }

            return $user;
        }

        $clinic = Clinic::query()->with('plan')->find($clinicId);
        if ($clinic && ! $actor->hasRole('super_admin') && ! $clinic->canAddUser()) {
            throw ValidationException::withMessages([
                'email' => __('settings.users_error_plan_limit'),
            ]);
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));

        $user = User::query()->create([
            'name' => $data['full_name'],
            'email' => $email,
            'password' => (string) $data['password'],
            'clinic_id' => $clinicId,
        ]);
        $user->assignRole('doctor');

        return $user;
    }
}
