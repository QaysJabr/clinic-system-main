<?php

namespace Tests\Support;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\TestResponse;

trait ApiTestHelpers
{
    protected string $apiToken = '';

    protected User $apiUser;

    protected function seedApiDefaults(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->apiUser = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->apiToken = (string) $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->json('data.token');
    }

    protected function apiGet(string $uri, array $headers = []): TestResponse
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->apiToken)
            ->getJson($uri, $headers);
    }

    protected function apiPost(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->apiToken)
            ->postJson($uri, $data, $headers);
    }
}
