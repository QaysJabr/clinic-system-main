<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_url_redirects_to_register_clinic(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('saas.register-clinic.create'));
    }

    public function test_new_clinic_owner_can_register_via_register_clinic(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/register-clinic', [
            'clinic_name' => 'عيادة الاختبار',
            'owner_name' => 'Test Owner',
            'email' => 'owner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('saas.pricing', absolute: false));
    }
}
