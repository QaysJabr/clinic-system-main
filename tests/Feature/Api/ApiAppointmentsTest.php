<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->token = (string) $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->json('data.token');
    }

    public function test_appointments_index_requires_auth(): void
    {
        $this->getJson(route('api.v1.appointments.index'))->assertUnauthorized();
    }

    public function test_appointments_index_returns_today_by_default(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $patient = Patient::query()->create([
            'file_number' => 'API-001',
            'full_name' => 'مريض API',
            'status' => 'active',
            'clinic_id' => $admin->clinic_id,
        ]);

        $staff = Staff::query()->create([
            'full_name' => 'طبيب API',
            'role_type' => 'doctor',
            'status' => 'active',
            'clinic_id' => $admin->clinic_id,
        ]);
        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب API',
            'status' => 'active',
            'clinic_id' => $admin->clinic_id,
        ]);

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00',
            'status' => 'scheduled',
        ]);

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->subDay()->toDateString(),
            'start_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('api.v1.appointments.index'));

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }
}
