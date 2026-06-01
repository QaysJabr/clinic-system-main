<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_from_clinic_b_cannot_access_clinic_a_patient(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $clinicA = Clinic::query()->findOrFail(config('tenancy.default_clinic_id'));
        $clinicB = Clinic::query()->create([
            'name' => 'عيادة تجريبية ب',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        $this->assertNotSame((int) $clinicA->id, (int) $clinicB->id);

        $adminRole = Role::findByName('admin');

        $userB = User::factory()->create([
            'email' => 'tenant-b@test.local',
            'clinic_id' => $clinicB->id,
        ]);
        $userB->assignRole($adminRole);

        $patientA = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicA->id,
            'file_number' => 'ISO-A-001',
            'full_name' => 'مريض العيادة أ',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('patients', [
            'id' => $patientA->id,
            'clinic_id' => $clinicA->id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $userB->id,
            'clinic_id' => $clinicB->id,
        ]);
        $this->assertFalse($userB->hasRole('super_admin'));

        $userB->refresh();

        $this->actingAs($userB);

        $this->assertSame((int) $clinicB->id, (int) Auth::user()->clinic_id);
        $this->assertSame((int) $clinicA->id, (int) $patientA->fresh()->clinic_id);

        $this->assertTrue(Patient::hasGlobalScope(\App\Models\Scopes\TenantScope::class));

        $this->assertFalse(Auth::user()->hasRole('super_admin'));

        $this->assertSame(
            (int) $clinicB->id,
            (int) DB::table('users')->where('id', Auth::id())->value('clinic_id')
        );

        $rawSql = Patient::query()->whereKey($patientA->id)->toRawSql();
        $this->assertStringContainsString('clinic_id', $rawSql);

        $this->assertSame(0, Patient::query()->whereKey($patientA->id)->count());

        $this->get(route('patients.show', $patientA))->assertNotFound();
    }

    public function test_same_clinic_admin_can_access_own_patient(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $clinicId = config('tenancy.default_clinic_id');

        $adminRole = Role::findByName('admin');

        $user = User::factory()->create([
            'email' => 'tenant-same@test.local',
            'clinic_id' => $clinicId,
        ]);
        $user->assignRole($adminRole);

        $patient = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'file_number' => 'ISO-S-001',
            'full_name' => 'مريض نفس العيادة',
            'status' => 'active',
        ]);

        $this->actingAs($user)->get(route('patients.show', $patient))->assertOk();
    }
}
