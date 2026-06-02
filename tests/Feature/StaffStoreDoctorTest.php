<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StaffStoreDoctorTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_store_with_doctor_role_redirects_to_onboarding(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('staff.store'), [
                'full_name' => 'د. أحمد',
                'role_type' => 'doctor',
                'status' => 'active',
            ])
            ->assertRedirect(route('doctors.onboarding.create'))
            ->assertSessionHas('info');

        $this->assertNull(Staff::query()->where('full_name', 'د. أحمد')->first());
        $this->assertSame(0, Doctor::query()->count());
    }
}
