<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_admin_dashboard_renders_with_analytics(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-clinic-dashboard', false)
            ->assertSee(__('dashboard.section_pulse'), false)
            ->assertSee(__('dashboard.cta_full_reports'), false)
            ->assertDontSee(__('dashboard.section_accrual_title'), false);
    }

    public function test_receptionist_redirects_to_reception_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');

        $this->actingAs($receptionist)
            ->get(route('dashboard'))
            ->assertRedirect(route('reception.dashboard'));
    }
}
