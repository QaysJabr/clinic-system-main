<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class StaffIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_index_renders_with_legacy_compensation_profile_rows(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinicId = (int) config('tenancy.default_clinic_id');

        $staff = Staff::query()->create([
            'clinic_id' => $clinicId,
            'full_name' => 'موظف تجريبي',
            'role_type' => 'receptionist',
            'status' => 'active',
        ]);

        DB::table('staff_compensation_profiles')->insert([
            'clinic_id' => $clinicId,
            'staff_id' => $staff->id,
            'compensation_type' => 'fixed',
            'payment_cycle' => null,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('staff.index'))
            ->assertOk()
            ->assertSee('موظف تجريبي', false);
    }
}
