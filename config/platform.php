<?php

/**
 * Platform (super-admin) owner configuration.
 *
 * Credentials must come from the environment — never commit real values here.
 * In production, PLATFORM_OWNER_EMAIL is required (enforced by EnsurePlatformOwner).
 */
return [
    'owner_email' => env('PLATFORM_OWNER_EMAIL'),

    /**
     * Used only when seeding or rotating the platform owner account.
     * Leave unset in production after the initial `php artisan db:seed` if you manage passwords elsewhere.
     */
    'owner_password' => env('PLATFORM_OWNER_PASSWORD'),

    'owner_name' => env('PLATFORM_OWNER_NAME', 'Platform Owner'),

    /** Days before subscription_expires_at to flag "expiring soon" (dashboard + filters). */
    'expiring_soon_days' => (int) env('PLATFORM_EXPIRING_SOON_DAYS', 7),

    /** Email clinic owner when platform records a manual subscription payment. */
    'notify_owner_manual_payment' => filter_var(env('PLATFORM_NOTIFY_OWNER_MANUAL_PAYMENT', true), FILTER_VALIDATE_BOOL),
];
