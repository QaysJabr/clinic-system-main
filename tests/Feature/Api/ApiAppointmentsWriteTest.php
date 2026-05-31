<?php

namespace Tests\Feature\Api;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAppointmentsWriteTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->token = (string) $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->json('data.token');
    }

    public function test_can_create_appointment_via_api(): void
    {
        $patient = Patient::query()->create([
            'file_number' => 'API-W-001',
            'full_name' => 'مريض كتابة',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $staff = Staff::query()->create([
            'full_name' => 'طبيب كتابة',
            'role_type' => 'doctor',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);
        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب كتابة',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $date = now()->addDay()->toDateString();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson(route('api.v1.appointments.store'), [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => $date,
                'start_time' => '10:00',
                'status' => 'scheduled',
                'reason' => 'فحص',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.patient_id', $patient->id)
            ->assertJsonPath('data.status', 'scheduled');
    }
}
