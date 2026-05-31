<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Services\Emr\PatientQrService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_portal_shows_patient_summary_without_auth(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $patient = Patient::query()->create([
            'file_number' => 'PORT-001',
            'full_name' => 'Portal Patient',
            'phone' => '0599999999',
            'status' => 'active',
        ]);

        $doctor = Doctor::query()->create(['full_name' => 'Dr Portal', 'status' => 'active']);

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '10:15',
            'status' => 'scheduled',
        ]);

        Invoice::query()->create([
            'patient_id' => $patient->id,
            'invoice_number' => 'INV-PORT-1',
            'total' => 150,
            'paid' => 50,
            'status' => 'partial',
        ]);

        $plain = app(PatientQrService::class)->issueToken($patient);

        $this->get(route('portal.patient.show', ['token' => $plain]))
            ->assertOk()
            ->assertSee('Portal Patient', false)
            ->assertSee('Dr Portal', false)
            ->assertSee('INV-PORT-1', false);
    }

    public function test_invalid_portal_token_returns_not_found(): void
    {
        $this->get(route('portal.patient.show', ['token' => 'invalid-token']))
            ->assertNotFound();
    }

    public function test_staff_can_issue_portal_link(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $patient = Patient::query()->create([
            'file_number' => 'PORT-002',
            'full_name' => 'Link Patient',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('patients.portal-link', $patient))
            ->assertRedirect()
            ->assertSessionHas('patient_portal_url');
    }
}
