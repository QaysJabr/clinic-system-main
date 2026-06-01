<?php

namespace App\TestingLab;

use RuntimeException;

final class TestingLabGuard
{
    public static function assertSafeToRunCommand(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('lab:smoke is blocked when APP_ENV=production.');
        }

        if (! app()->environment(config('testing-lab.allowed_environments', ['local', 'testing']))) {
            throw new RuntimeException(
                'lab:smoke runs only when APP_ENV is local or testing. Current: '.app()->environment()
            );
        }

        // PHPUnit uses DB_* from phpunit.xml (clinic_test_db), not necessarily .env DB_DATABASE.
    }
}
