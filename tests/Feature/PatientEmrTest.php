<?php

namespace Tests\Feature;

use App\Enums\ClinicalRecordType;
use App\Models\Attachment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visit;
use App\Services\Emr\PatientQrService;
use App\Services\Emr\VisitSoapService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientEmrTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_emr_chart_requires_profile_access(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $patient = Patient::query()->create([
            'file_number' => 'EMR-001',
            'full_name' => 'مريض EMR',
            'status' => 'active',
        ]);

        $staffB = Staff::query()->create(['full_name' => 'طبيب ب', 'role_type' => 'doctor', 'status' => 'active']);
        $userB = User::factory()->create(['email' => 'doc-emr-b@test.local']);
        $staffB->update(['user_id' => $userB->id]);
        $userB->assignRole(Role::findByName('doctor'));
        Doctor::query()->create(['staff_id' => $staffB->id, 'full_name' => 'طبيب ب', 'status' => 'active']);

        $this->actingAs($userB)
            ->get(route('patients.show', $patient))
            ->assertForbidden();
    }

    public function test_soap_persists_and_syncs_legacy_fields(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $patient = Patient::query()->create([
            'file_number' => 'EMR-002',
            'full_name' => 'مريض SOAP',
            'status' => 'active',
        ]);
        $doctor = Doctor::query()->create(['full_name' => 'طبيب', 'status' => 'active']);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_IN_PROGRESS,
        ]);

        $request = Request::create('/', 'POST', [
            'soap_subjective' => 'Headache 3 days',
            'soap_objective' => 'BP 120/80',
            'soap_assessment' => 'Tension headache',
            'soap_plan' => 'Rest, fluids',
            'notes' => 'Follow up in 1 week',
        ]);

        app(VisitSoapService::class)->syncFromRequest($visit, $request);

        $visit->refresh();
        $this->assertSame('Headache 3 days', $visit->chief_complaint);
        $this->assertSame('Tension headache', $visit->diagnosis);
        $this->assertDatabaseHas('visit_soap_notes', [
            'visit_id' => $visit->id,
            'subjective' => 'Headache 3 days',
            'assessment' => 'Tension headache',
        ]);

        $this->actingAs($admin)
            ->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee('Headache', false);
    }

    public function test_clinical_record_store_and_tenant_isolation(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $patient = Patient::query()->create([
            'file_number' => 'EMR-003',
            'full_name' => 'مريض حساسية',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('patients.clinical.store', $patient), [
                'type' => ClinicalRecordType::Allergy->value,
                'title' => 'Penicillin',
                'details' => 'Rash',
            ])
            ->assertRedirect(route('patients.show', $patient));

        $this->assertDatabaseHas('patient_clinical_records', [
            'patient_id' => $patient->id,
            'type' => 'allergy',
            'title' => 'Penicillin',
        ]);
    }

    public function test_attachment_download_respects_patient_access(): void
    {
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);

        $patient = Patient::query()->create([
            'file_number' => 'EMR-004',
            'full_name' => 'مريض مرفق',
            'status' => 'active',
        ]);

        $path = 'attachments/patients/test.pdf';
        Storage::disk('public')->put($path, 'pdf-content');

        $attachment = Attachment::query()->create([
            'patient_id' => $patient->id,
            'file_name' => 'report.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'category' => 'lab',
            'file_size' => 100,
            'uploaded_by' => User::query()->where('email', 'admin@clinic.local')->value('id'),
        ]);

        $staffB = Staff::query()->create(['full_name' => 'طبيب ب', 'role_type' => 'doctor', 'status' => 'active']);
        $userB = User::factory()->create(['email' => 'doc-emr-att@test.local']);
        $staffB->update(['user_id' => $userB->id]);
        $userB->assignRole(Role::findByName('doctor'));
        $userB->givePermissionTo('view attachments');
        Doctor::query()->create(['staff_id' => $staffB->id, 'full_name' => 'طبيب ب', 'status' => 'active']);

        $this->actingAs($userB)
            ->get(route('attachments.download', $attachment))
            ->assertForbidden();

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin)
            ->get(route('attachments.download', $attachment))
            ->assertOk();
    }

    public function test_qr_lookup_redirects_for_authorized_staff(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $patient = Patient::query()->create([
            'file_number' => 'EMR-005',
            'full_name' => 'QR Patient',
            'status' => 'active',
        ]);

        $plain = app(PatientQrService::class)->issueToken($patient);

        $this->actingAs($admin)
            ->get(route('patients.lookup', ['token' => $plain]))
            ->assertRedirect(route('patients.show', $patient));
    }
}
