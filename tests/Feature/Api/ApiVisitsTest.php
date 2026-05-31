<?php

namespace Tests\Feature\Api;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiVisitsTest extends TestCase
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

    public function test_visits_index_defaults_to_today(): void
    {
        $patient = Patient::query()->create([
            'file_number' => 'API-V-001',
            'full_name' => 'مريض زيارة',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $staff = Staff::query()->create([
            'full_name' => 'طبيب زيارة',
            'role_type' => 'doctor',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);
        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب زيارة',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);

        Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
            'clinic_id' => $this->admin->clinic_id,
        ]);

        Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_date' => now()->subDay()->toDateString(),
            'status' => Visit::STATUS_WAITING,
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('api.v1.visits.index'))
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_can_patch_visit_status(): void
    {
        $patient = Patient::query()->create([
            'file_number' => 'API-V-002',
            'full_name' => 'مريض حالة',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $staff = Staff::query()->create([
            'full_name' => 'طبيب حالة',
            'role_type' => 'doctor',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);
        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب حالة',
            'status' => 'active',
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
            'clinic_id' => $this->admin->clinic_id,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson(route('api.v1.visits.status', $visit), [
                'status' => Visit::STATUS_IN_PROGRESS,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Visit::STATUS_IN_PROGRESS);
    }
}
