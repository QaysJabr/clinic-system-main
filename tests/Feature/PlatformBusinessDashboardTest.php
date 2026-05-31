<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformBusinessDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);

        return User::query()
            ->where('email', config('platform.owner_email'))
            ->firstOrFail();
    }

    public function test_platform_dashboard_renders_with_metrics(): void
    {
        $super = $this->superAdmin();

        $clinic = Clinic::query()->firstOrFail();
        SubscriptionPayment::query()->create([
            'clinic_id' => $clinic->id,
            'amount' => 100.50,
            'paid_at' => now(),
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
            'source' => SubscriptionPayment::SOURCE_MANUAL,
        ]);

        $this->actingAs($super);

        $response = $this->get(route('platform.dashboard'));
        $response->assertOk();
        $response->assertSee('إجمالي الإيرادات', false);
        $response->assertSee('100.50', false);
        $response->assertSee('إجمالي العيادات', false);
        $response->assertSee('يدوي', false);
        $response->assertSee('تنتهي خلال', false);
    }

    public function test_edit_clinic_redirects_to_subscription(): void
    {
        $super = $this->superAdmin();
        $clinic = Clinic::query()->firstOrFail();

        $this->actingAs($super);

        $this->get(route('platform.clinics.edit', $clinic))
            ->assertRedirect(route('platform.clinics.subscription', $clinic));
    }

    public function test_super_admin_can_activate_clinic_and_record_payment(): void
    {
        $super = $this->superAdmin();
        $clinic = Clinic::query()->firstOrFail();

        $this->actingAs($super);

        $this->post(route('platform.clinics.activate', $clinic))
            ->assertRedirect(route('platform.clinics.index'));

        $this->assertTrue($clinic->fresh()->is_active);

        $this->post(route('platform.clinics.record-payment', $clinic), [
            'amount' => 25,
            'notes' => 'test payment',
        ])->assertRedirect(route('platform.clinics.subscription', $clinic));

        $this->assertDatabaseHas('subscription_payments', [
            'clinic_id' => $clinic->id,
            'amount' => '25.00',
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
        ]);
    }

    public function test_subscription_management_page_renders(): void
    {
        $super = $this->superAdmin();
        $clinic = Clinic::query()->firstOrFail();

        $this->actingAs($super);

        $this->get(route('platform.clinics.subscription', $clinic))
            ->assertOk()
            ->assertSee('إدارة الاشتراك', false)
            ->assertSee($clinic->name, false)
            ->assertSee('إعدادات العيادة والاشتراك', false)
            ->assertSee('سجل المدفوعات', false)
            ->assertSee('من تاريخ', false);
    }

    public function test_non_super_admin_cannot_access_platform_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'clinic_id' => Clinic::query()->value('id'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $this->get(route('platform.dashboard'))->assertForbidden();
    }
}
