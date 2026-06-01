<?php

namespace Tests\Feature\Api;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ApiTestHelpers;
use Tests\TestCase;

final class ApiPatientsTest extends TestCase
{
    use ApiTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedApiDefaults();
    }

    public function test_patients_index_requires_auth(): void
    {
        $this->getJson(route('api.v1.patients.index'))->assertUnauthorized();
    }

    public function test_patients_index_returns_paginated_list(): void
    {
        Patient::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'file_number' => 'API-P-001',
            'full_name' => 'مريض API',
            'status' => 'active',
        ]);

        $this->apiGet(route('api.v1.patients.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    }

    public function test_can_create_patient_via_api_without_manual_file_number(): void
    {
        $response = $this->apiPost(route('api.v1.patients.store'), [
            'full_name' => 'مريض جديد API',
            'phone' => '0591111111',
            'gender' => 'male',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.full_name', 'مريض جديد API');

        $this->assertDatabaseHas('patients', [
            'clinic_id' => $this->apiUser->clinic_id,
            'full_name' => 'مريض جديد API',
        ]);
    }

    public function test_cannot_view_patient_from_other_clinic(): void
    {
        $clinicB = Clinic::query()->create([
            'name' => 'عيادة ب API',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        $otherPatient = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicB->id,
            'file_number' => 'API-OTHER-1',
            'full_name' => 'مريض عيادة أخرى',
            'status' => 'active',
        ]);

        $this->apiGet(route('api.v1.patients.show', $otherPatient))->assertNotFound();
    }

    public function test_subscription_inactive_returns_json_for_api_dashboard(): void
    {
        Clinic::query()->whereKey($this->apiUser->clinic_id)->update([
            'subscription_status' => Clinic::STATUS_EXPIRED,
            'subscription_expires_at' => now()->subDay(),
        ]);

        $this->apiGet(route('api.v1.dashboard'))
            ->assertForbidden()
            ->assertJsonPath('code', 'subscription_inactive');
    }
}
