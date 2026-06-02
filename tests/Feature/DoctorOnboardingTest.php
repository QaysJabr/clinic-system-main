<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DoctorOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_onboard_doctor_with_single_email(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('doctors.onboarding.store'), [
            'account_mode' => 'new',
            'full_name' => 'د. سارة',
            'email' => 'sara.doctor@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0599000001',
            'status' => 'active',
            'specialty' => 'طب عام',
            'room_number' => '2',
        ]);

        $response->assertRedirect();

        $user = User::query()->where('email', 'sara.doctor@test.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('doctor'));

        $staff = Staff::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($staff);
        $this->assertSame('sara.doctor@test.local', $staff->email);

        $doctor = Doctor::query()->where('staff_id', $staff->id)->first();
        $this->assertNotNull($doctor);
        $this->assertSame('sara.doctor@test.local', $doctor->email);
        $this->assertSame('د. سارة', $doctor->full_name);
    }

    public function test_doctors_create_redirects_to_onboarding(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('doctors.create'))
            ->assertRedirect(route('doctors.onboarding.create'));
    }
}
