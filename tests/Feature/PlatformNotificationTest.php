<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use App\Support\AppNotificationType;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);

        return User::query()
            ->where('email', config('platform.owner_email'))
            ->firstOrFail();
    }

    public function test_super_admin_can_view_platform_notifications_page(): void
    {
        $super = $this->superAdmin();

        AppNotification::query()->create([
            'user_id' => $super->id,
            'clinic_id' => config('tenancy.default_clinic_id'),
            'type' => AppNotificationType::PLATFORM_CLINIC_MANAGED,
            'title' => 'Platform alert',
            'message' => 'Clinic updated',
            'is_read' => false,
        ]);

        $this->actingAs($super)
            ->get(route('platform.notifications.index'))
            ->assertOk()
            ->assertSee('إشعارات المنصّة', false)
            ->assertSee('Platform alert', false);
    }

    public function test_super_admin_cannot_access_clinic_notifications_route(): void
    {
        $super = $this->superAdmin();

        $this->actingAs($super)
            ->get(route('notifications.index'))
            ->assertForbidden();
    }

    public function test_clinic_admin_does_not_see_platform_notifications_in_clinic_list(): void
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);

        $admin = User::query()
            ->where('email', config('seeding.admin_email'))
            ->firstOrFail();

        AppNotification::query()->create([
            'user_id' => $admin->id,
            'clinic_id' => $admin->clinic_id,
            'type' => AppNotificationType::PLATFORM_CLINIC_MANAGED,
            'title' => 'Should be hidden',
            'message' => 'Platform only',
            'is_read' => false,
        ]);

        AppNotification::query()->create([
            'user_id' => $admin->id,
            'clinic_id' => $admin->clinic_id,
            'type' => AppNotificationType::PATIENT_REGISTERED,
            'title' => 'Clinic alert',
            'message' => 'New patient',
            'is_read' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Clinic alert', false)
            ->assertDontSee('Should be hidden', false);
    }
}
