<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StaffUpdateDoctorClinicalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_doctor_clinical_fields_on_staff_edit(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinicId = (int) config('tenancy.default_clinic_id');

        $staff = Staff::query()->create([
            'clinic_id' => $clinicId,
            'full_name' => 'د. قيس',
            'role_type' => 'doctor',
            'status' => 'active',
            'email' => 'q@q.com',
            'phone' => '0595604495',
        ]);

        $this->actingAs($admin)->put(route('staff.update', $staff), [
            'full_name' => 'د. قيس',
            'role_type' => 'doctor',
            'status' => 'active',
            'phone' => '0595604495',
            'email' => 'q@q.com',
            'specialty' => 'طب أسنان',
            'room_number' => '5',
            'license_number' => 'LIC-100',
            'notes' => 'ملاحظة تجريبية',
        ])->assertRedirect(route('staff.index'));

        $doctor = Doctor::query()->withoutGlobalScopes()->where('staff_id', $staff->id)->first();
        $this->assertNotNull($doctor);
        $this->assertSame('طب أسنان', $doctor->specialty);
        $this->assertSame('5', $doctor->room_number);
        $this->assertSame('LIC-100', $doctor->license_number);
        $this->assertSame('ملاحظة تجريبية', $doctor->notes);
    }

    public function test_admin_can_update_doctor_with_only_specialty_filled(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinicId = (int) config('tenancy.default_clinic_id');

        $staff = Staff::query()->create([
            'clinic_id' => $clinicId,
            'full_name' => 'د. قيس',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);

        Doctor::query()->withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'staff_id' => $staff->id,
            'full_name' => $staff->full_name,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->put(route('staff.update', $staff), [
            'full_name' => 'د. قيس',
            'role_type' => 'doctor',
            'status' => 'active',
            'specialty' => 'جلدية',
            'license_number' => '',
            'room_number' => '',
            'notes' => '',
        ])->assertRedirect(route('staff.index'));

        $doctor = Doctor::query()->withoutGlobalScopes()->where('staff_id', $staff->id)->first();
        $this->assertSame('جلدية', $doctor?->specialty);
        $this->assertNull($doctor?->license_number);
    }
}
