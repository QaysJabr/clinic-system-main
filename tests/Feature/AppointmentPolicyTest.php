<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_cannot_edit_other_doctors_appointment(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctorRole = Role::findByName('doctor');
        $patient = Patient::query()->create([
            'file_number' => 'AP-001',
            'full_name' => 'مريض',
            'status' => 'active',
        ]);

        $staffA = Staff::query()->create(['full_name' => 'طبيب أ', 'role_type' => 'doctor', 'status' => 'active']);
        $userA = User::factory()->create(['email' => 'doc-ap-a@test.local']);
        $staffA->update(['user_id' => $userA->id]);
        $userA->assignRole($doctorRole);
        $docA = Doctor::query()->create(['staff_id' => $staffA->id, 'full_name' => 'طبيب أ', 'status' => 'active']);

        $staffB = Staff::query()->create(['full_name' => 'طبيب ب', 'role_type' => 'doctor', 'status' => 'active']);
        $docB = Doctor::query()->create(['staff_id' => $staffB->id, 'full_name' => 'طبيب ب', 'status' => 'active']);

        $own = Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $docA->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00',
            'status' => 'scheduled',
        ]);

        $other = Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $docB->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($userA)->get(route('appointments.edit', $own))->assertOk();
        $this->actingAs($userA)->get(route('appointments.edit', $other))->assertForbidden();
    }
}
