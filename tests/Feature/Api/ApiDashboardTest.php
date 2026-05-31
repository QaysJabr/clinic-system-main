<?php

namespace Tests\Feature\Api;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDashboardTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->token = (string) $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->json('data.token');
    }

    public function test_meta_is_public(): void
    {
        $this->getJson(route('api.v1.meta'))
            ->assertOk()
            ->assertJsonPath('data.api_version', '1.0.0')
            ->assertJsonPath('data.auth.type', 'bearer');
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->getJson(route('api.v1.dashboard'))->assertUnauthorized();
    }

    public function test_dashboard_returns_stats(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('api.v1.dashboard'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'persona',
                    'stats' => [
                        'today_appointments',
                        'today_visits',
                        'today_visits_waiting',
                    ],
                    'today_appointments',
                    'visit_queue',
                ],
            ]);
    }
}
