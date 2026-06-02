<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserDoctorOnboardingRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_user_role_to_doctor_without_staff_redirects_to_onboarding(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $user = User::query()->create([
            'name' => 'موظف استقبال',
            'email' => 'desk@test.local',
            'password' => 'password123',
            'clinic_id' => config('tenancy.default_clinic_id'),
        ]);
        $user->assignRole('receptionist');

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'doctor',
            ])
            ->assertRedirect(route('doctors.onboarding.create', [
                'account_mode' => 'existing',
                'user_id' => $user->id,
                'name' => $user->name,
            ]))
            ->assertSessionHas('info');

        $user->refresh();
        $this->assertTrue($user->hasRole('doctor'));
        $this->assertFalse($user->staffRecord()->exists());
    }
}
