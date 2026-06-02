<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserCreateFromStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_from_existing_doctor_staff(): void
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

        Doctor::query()->withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'staff_id' => $staff->id,
            'full_name' => $staff->full_name,
            'email' => $staff->email,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'account_mode' => 'from_staff',
                'staff_id' => $staff->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $user = User::query()->where('email', 'q@q.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('doctor'));

        $staff->refresh();
        $this->assertSame($user->id, $staff->user_id);
    }

    public function test_creating_doctor_as_new_account_redirects_to_onboarding(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'account_mode' => 'new',
                'name' => 'د. جديد',
                'email' => 'newdoc@test.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'doctor',
            ])
            ->assertRedirect(route('doctors.onboarding.create'))
            ->assertSessionHas('info');

        $this->assertNull(User::query()->where('email', 'newdoc@test.local')->first());
    }
}
