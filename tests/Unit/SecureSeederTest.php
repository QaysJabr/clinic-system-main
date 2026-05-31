<?php

namespace Tests\Unit;

use App\Support\SecureSeeder;
use Tests\TestCase;

class SecureSeederTest extends TestCase
{
    public function test_rejects_weak_password_when_insecure_not_allowed(): void
    {
        config(['seeding.allow_insecure_defaults' => false]);

        $this->expectException(\RuntimeException::class);

        SecureSeeder::password('password', 'test');
    }
}
