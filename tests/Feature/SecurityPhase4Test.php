<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Security\TwoFactorService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecurityPhase4Test extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_checks(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonStructure(['status', 'checks', 'timestamp']);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_two_factor_challenge_redirect_when_enabled(): void
    {
        config(['security.two_factor.enabled' => true]);

        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $secret = app(TwoFactorService::class)->generateSecret();
        $admin->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => json_encode([]),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('two-factor.challenge'));
    }

    public function test_two_factor_verify_marks_session(): void
    {
        config(['security.two_factor.enabled' => true]);

        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $secret = app(TwoFactorService::class)->generateSecret();
        $code = (new Google2FA)->getCurrentOtp($secret);

        $admin->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => json_encode([]),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->post(route('two-factor.challenge.verify'), ['code' => $code])
            ->assertRedirect();

        $this->assertTrue(session(TwoFactorService::SESSION_VERIFIED));
    }

    public function test_sessions_page_lists_for_authenticated_user(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('security.sessions.index'))
            ->assertOk();
    }

    public function test_login_records_failed_attempt(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->post('/login', [
            'email' => 'admin@clinic.local',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('login_attempts', [
            'email' => 'admin@clinic.local',
            'successful' => false,
        ]);
    }
}
