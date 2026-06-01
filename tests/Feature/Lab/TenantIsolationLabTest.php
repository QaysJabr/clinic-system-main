<?php

namespace Tests\Feature\Lab;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Cross-tenant: clinic B user must not see clinic A patient.
 */
final class TenantIsolationLabTest extends LabTestCase
{
    public function test_clinic_b_admin_cannot_open_clinic_a_patient(): void
    {
        $clinicAId = (int) $this->clinicAdmin->clinic_id;

        $clinicB = Clinic::query()->create([
            'name' => 'عيادة Lab ب',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        $userB = User::factory()->create([
            'email' => 'lab-tenant-b@test.local',
            'clinic_id' => $clinicB->id,
            'email_verified_at' => now(),
        ]);
        $userB->assignRole(Role::findByName('admin', 'web'));

        $patientA = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicAId,
            'file_number' => 'LAB-A-001',
            'full_name' => 'مريض عيادة أ',
            'status' => 'active',
        ]);

        $this->actingAs($userB)
            ->get(route('patients.show', $patientA))
            ->assertNotFound();
    }
}
