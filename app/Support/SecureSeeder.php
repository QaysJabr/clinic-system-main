<?php

namespace App\Support;

use RuntimeException;

/**
 * Production-safe password resolution for database seeders.
 */
final class SecureSeeder
{
    private const FORBIDDEN = ['password', '123456', 'admin', 'secret', 'changeme'];

    public static function allowInsecureDefaults(): bool
    {
        return (bool) config('seeding.allow_insecure_defaults', false);
    }

    /**
     * Resolve a password for seeding. In production, requires a strong env-provided value.
     */
    public static function password(?string $candidate, string $context, int $minLength = 12): string
    {
        $candidate = is_string($candidate) ? trim($candidate) : '';

        if (app()->environment('production')) {
            self::assertProductionPassword($candidate, $context, $minLength);

            return $candidate;
        }

        if ($candidate !== '' && ! self::isForbidden($candidate)) {
            return $candidate;
        }

        if (self::allowInsecureDefaults()) {
            return $candidate !== '' ? $candidate : 'password';
        }

        throw new RuntimeException(
            "Seeding [{$context}] requires SEED_ADMIN_PASSWORD or PLATFORM_OWNER_PASSWORD (min {$minLength} chars). "
            .'Set SEED_ALLOW_INSECURE=true only for local development.'
        );
    }

    public static function assertProductionPassword(string $password, string $context, int $minLength = 12): void
    {
        if ($password === '' || strlen($password) < $minLength) {
            throw new RuntimeException(
                "Production seeding [{$context}] requires a password of at least {$minLength} characters via environment variables."
            );
        }

        if (self::isForbidden($password)) {
            throw new RuntimeException(
                "Production seeding [{$context}] cannot use a known weak password. Choose a unique strong password."
            );
        }
    }

    public static function isForbidden(string $password): bool
    {
        return in_array(strtolower($password), self::FORBIDDEN, true);
    }

    public static function blockProductionDemoSeed(): void
    {
        if (app()->environment('production') && ! (bool) config('seeding.demo_enabled', false)) {
            throw new RuntimeException(
                'Demo seeders are disabled in production. Set SEED_DEMO_DATA=true only on intentional demo environments.'
            );
        }
    }
}
