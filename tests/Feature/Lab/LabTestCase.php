<?php

namespace Tests\Feature\Lab;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class LabTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $clinicAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->clinicAdmin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
    }

    protected function actingAsClinicAdmin(): static
    {
        return $this->actingAs($this->clinicAdmin);
    }
}
