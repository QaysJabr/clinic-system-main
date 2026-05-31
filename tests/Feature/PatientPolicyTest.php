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

class PatientPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_cannot_edit_unrelated_patient(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctorRole = Role::findByName('doctor');

        $staff = Staff::query()->create([
            'full_name' => 'طبيب',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['email' => 'doc-patient-policy@test.local']);
        $staff->update(['user_id' => $user->id]);
        $user->assignRole($doctorRole);

        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب',
            'status' => 'active',
        ]);

        $related = Patient::query()->create([
            'file_number' => 'PP-001',
            'full_name' => 'مريض مرتبط',
            'status' => 'active',
        ]);

        $unrelated = Patient::query()->create([
            'file_number' => 'PP-002',
            'full_name' => 'مريض غير مرتبط',
            'status' => 'active',
        ]);

        Visit::query()->create([
            'patient_id' => $related->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
        ]);

        $this->actingAs($user)->get(route('patients.edit', $related))->assertOk();
        $this->actingAs($user)->get(route('patients.edit', $unrelated))->assertForbidden();
    }
}
