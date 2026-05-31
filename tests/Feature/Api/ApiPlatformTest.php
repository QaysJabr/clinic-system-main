<?php

namespace Tests\Feature\Api;

use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPlatformTest extends TestCase
{
    use RefreshDatabase;

    private function platformOwnerToken(): string
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => config('platform.owner_email'),
            'password' => config('platform.owner_password', 'password'),
            'device_name' => 'phpunit',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.persona', 'platform');

        return (string) $response->json('data.token');
    }

    public function test_platform_owner_can_login_via_mobile_api(): void
    {
        $token = $this->platformOwnerToken();
        $this->assertNotSame('', $token);
    }

    public function test_clinic_admin_cannot_access_platform_dashboard_api(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $login = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$login->json('data.token'))
            ->getJson(route('api.v1.platform.dashboard'))
            ->assertForbidden();
    }

    public function test_platform_owner_can_load_dashboard_and_manage_clinic(): void
    {
        $token = $this->platformOwnerToken();
        $clinic = Clinic::query()->firstOrFail();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.v1.platform.dashboard'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_revenue',
                    'monthly_revenue',
                    'total_clinics',
                    'recent_payments',
                    'clinics_expiring_soon',
                ],
            ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.v1.platform.clinics.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(route('api.v1.platform.clinics.record-payment', $clinic), [
                'amount' => 50,
                'notes' => 'mobile api test',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment.amount', '50.00');

        $this->assertDatabaseHas('subscription_payments', [
            'clinic_id' => $clinic->id,
            'amount' => '50.00',
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
        ]);
    }
}
