<?php

namespace Tests\Feature\Lab;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Quick smoke: each clinic role reaches its home pages.
 */
final class RoleSmokeLabTest extends LabTestCase
{
    public function test_doctor_receptionist_accountant_core_pages(): void
    {
        $clinicId = (int) $this->clinicAdmin->clinic_id;

        $doctor = $this->makeUser('doctor', 'lab-doc@test.local', $clinicId, true);
        $reception = $this->makeUser('receptionist', 'lab-rec@test.local', $clinicId);
        $accountant = $this->makeUser('accountant', 'lab-acc@test.local', $clinicId);

        $this->actingAs($doctor)->followingRedirects()->get(route('clinical.dashboard'))->assertOk();
        $this->actingAs($doctor)->get(route('invoices.index'))->assertForbidden();

        $this->actingAs($reception)->followingRedirects()->get(route('reception.dashboard'))->assertOk();
        $this->actingAs($reception)->followingRedirects()->get(route('patients.index'))->assertOk();

        $this->actingAs($accountant)->followingRedirects()->get(route('invoices.index'))->assertOk();
        $this->actingAs($accountant)->get(route('clinical.dashboard'))->assertForbidden();
    }

    private function makeUser(string $role, string $email, int $clinicId, bool $linkDoctor = false): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password'),
            'clinic_id' => $clinicId,
            'email_verified_at' => now(),
        ]);
        $user->assignRole(Role::findByName($role, 'web'));

        if ($linkDoctor) {
            $staff = Staff::query()->create([
                'clinic_id' => $clinicId,
                'user_id' => $user->id,
                'full_name' => 'طبيب Lab',
                'role_type' => 'doctor',
                'status' => 'active',
            ]);
            Doctor::query()->create([
                'clinic_id' => $clinicId,
                'staff_id' => $staff->id,
                'full_name' => 'طبيب Lab',
                'status' => 'active',
            ]);
        }

        return $user->fresh();
    }
}
