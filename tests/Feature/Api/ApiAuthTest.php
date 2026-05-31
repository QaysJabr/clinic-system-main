<?php

namespace Tests\Feature\Api;

use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_returns_bearer_token_for_clinic_admin(): void
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'expires_at', 'user' => ['id', 'email', 'clinic']],
            ])
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson(route('api.v1.auth.me'))->assertUnauthorized();
    }

    public function test_me_returns_user_with_valid_token(): void
    {
        $login = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->assertCreated();

        $token = $login->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.v1.auth.me'))
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@clinic.local');
    }

    public function test_logout_revokes_current_token(): void
    {
        $login = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->assertCreated();

        $token = (string) $login->json('data.token');
        $this->assertNotNull(PersonalAccessToken::findToken($token));

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(route('api.v1.auth.logout'))
            ->assertOk();

        $this->assertNull(PersonalAccessToken::findToken($token));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
