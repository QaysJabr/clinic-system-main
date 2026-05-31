<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VisitPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_cannot_view_other_doctor_visit(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctorRole = Role::findByName('doctor');

        $staffA = Staff::query()->create([
            'full_name' => 'طبيب أ',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);
        $userA = User::factory()->create(['email' => 'doc-a@test.local']);
        $staffA->update(['user_id' => $userA->id]);
        $userA->assignRole($doctorRole);

        $staffB = Staff::query()->create([
            'full_name' => 'طبيب ب',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);
        $userB = User::factory()->create(['email' => 'doc-b@test.local']);
        $staffB->update(['user_id' => $userB->id]);
        $userB->assignRole($doctorRole);

        $docA = Doctor::query()->create([
            'staff_id' => $staffA->id,
            'full_name' => 'طبيب أ',
            'status' => 'active',
        ]);
        $docB = Doctor::query()->create([
            'staff_id' => $staffB->id,
            'full_name' => 'طبيب ب',
            'status' => 'active',
        ]);

        $patient = Patient::query()->create([
            'file_number' => 'T-001',
            'full_name' => 'مريض تجريبي',
            'status' => 'active',
        ]);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $docA->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
        ]);

        $this->actingAs($userB)->get(route('visits.show', $visit))->assertForbidden();
    }

    public function test_admin_can_view_any_visit(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $staff = Staff::query()->create([
            'full_name' => 'طبيب',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);
        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب',
            'status' => 'active',
        ]);

        $patient = Patient::query()->create([
            'file_number' => 'T-002',
            'full_name' => 'مريض',
            'status' => 'active',
        ]);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
        ]);

        $this->actingAs($admin)->get(route('visits.show', $visit))->assertOk();
    }
}
