<?php

namespace Tests\Feature;

use App\Models\Appointment;
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

    public function test_doctor_sees_only_related_patients_via_visits_or_appointments(): void
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

        $patientByAppointmentOnly = Patient::query()->create([
            'file_number' => 'S-002',
            'full_name' => 'مريض بموعد فقط',
            'status' => 'active',
        ]);

        $patientNobody = Patient::query()->create([
            'file_number' => 'S-003',
            'full_name' => 'مريض غير مرتبط',
            'status' => 'active',
        ]);

        Visit::query()->create([
            'patient_id' => $patientOnlyA->id,
            'doctor_id' => $docA->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
        ]);

        Appointment::query()->create([
            'patient_id' => $patientByAppointmentOnly->id,
            'doctor_id' => $docA->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '10:00',
            'status' => 'scheduled',
        ]);

        // إشعارات تسجيل المرضى تظهر في الهيدر وتتضمن الاسم — نزيلها حتى لا تُفسِد assertDontSee على جدول المرضى.
        DB::table('app_notifications')->delete();

        $response = $this->actingAs($userA)->get(route('patients.index'));

        $response->assertOk();
        $response->assertSee('مريض خاص بالطبيب أ');
        $response->assertSee('مريض بموعد فقط');
        $response->assertDontSee('مريض غير مرتبط');
    }

    public function test_doctor_visit_create_patient_dropdown_is_scoped_to_related_patients(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctorRole = Role::findByName('doctor');

        $staffA = Staff::query()->create([
            'full_name' => 'طبيب أ',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);
        $userA = User::factory()->create(['email' => 'scope-visits@test.local']);
        $staffA->update(['user_id' => $userA->id]);
        $userA->assignRole($doctorRole);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $userA = $userA->fresh();

        $docA = Doctor::query()->create([
            'staff_id' => $staffA->id,
            'full_name' => 'طبيب أ',
            'status' => 'active',
        ]);

        $patientVisit = Patient::query()->create([
            'file_number' => 'V-001',
            'full_name' => 'مريض زيارة',
            'status' => 'active',
        ]);
        $patientAppointment = Patient::query()->create([
            'file_number' => 'V-002',
            'full_name' => 'مريض موعد',
            'status' => 'active',
        ]);
        $patientOther = Patient::query()->create([
            'file_number' => 'V-003',
            'full_name' => 'مريض خارج النطاق',
            'status' => 'active',
        ]);

        Visit::query()->create([
            'patient_id' => $patientVisit->id,
            'doctor_id' => $docA->id,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_WAITING,
        ]);

        Appointment::query()->create([
            'patient_id' => $patientAppointment->id,
            'doctor_id' => $docA->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '11:00',
            'status' => 'scheduled',
        ]);

        DB::table('app_notifications')->delete();

        $response = $this->actingAs($userA)->get(route('visits.create'));
        $response->assertOk();
        $response->assertSee('مريض زيارة');
        $response->assertSee('مريض موعد');
        $response->assertDontSee('مريض خارج النطاق');
    }
}
