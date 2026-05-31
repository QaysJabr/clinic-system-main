<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'security.two_factor.enforce_platform_owner' => false,
            'security.two_factor.enforce_roles' => [],
            'seeding.allow_insecure_defaults' => true,
        ]);

        // HTTP tests use post()/put() without embedding _token; disable CSRF in the test harness only.
        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}
