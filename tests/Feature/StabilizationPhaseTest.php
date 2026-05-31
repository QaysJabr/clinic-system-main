<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StabilizationPhaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_dashboard_passes_without_2fa_redirect(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $super = User::query()->where('email', config('platform.owner_email'))->firstOrFail();
        $super->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($super)
            ->get(route('platform.dashboard'))
            ->assertOk();
    }

    public function test_email_verification_disabled_by_default(): void
    {
        $this->assertFalse(config('security.email_verification.enabled'));
        $this->assertFalse(config('security.two_factor.enabled'));
    }
}
