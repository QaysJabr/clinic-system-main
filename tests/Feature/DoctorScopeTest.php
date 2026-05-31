<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DoctorScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_sees_only_patients_with_own_visits(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctorRole = Role::findByName('doctor');

        $staffA = Staff::query()->create([
            'full_name' => 'طبيب أ',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);
        $userA = User::factory()->create(['email' => 'scope-a@test.local']);
        $staffA->update(['user_id' => $userA->id]);
        $userA->assignRole($doctorRole);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $userA = $userA->fresh();

        $docA = Doctor::query()->create([
            'staff_id' => $staffA->id,
            'full_name' => 'طبيب أ',
            'status' => 'active',
        ]);

        $patientOnlyA = Patient::query()->create([
            'file_number' => 'S-001',
            'full_name' => 'مريض خاص بالطبيب أ',
            'status' => 'active',
        ]);

        $patientNobody = Patient::query()->create([
            'file_number' => 'S-002',
            'full_name' => 'مريض بدون زيارات',
            'status' => 'active',
        ]);

        Visit::query()->create([
            'patient_id' => $patientOnlyA->id,
            'doctor_id' => $docA->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
        ]);

        // إشعارات تسجيل المرضى تظهر في الهيدر وتتضمن الاسم — نزيلها حتى لا تُفسِد assertDontSee على جدول المرضى.
        DB::table('app_notifications')->delete();

        $response = $this->actingAs($userA)->get(route('patients.index'));

        $response->assertOk();
        $response->assertSee('مريض خاص بالطبيب أ');
        $response->assertDontSee('مريض بدون زيارات');
    }
}
