<?php

return [

    /**
     * Allow literal "password" defaults outside production (local CI uses this).
     */
    'allow_insecure_defaults' => (bool) env('SEED_ALLOW_INSECURE', false),

    /**
     * Enable MultiClinicDemoSeeder and similar demo datasets.
     */
    'demo_enabled' => (bool) env('SEED_DEMO_DATA', false),

    /**
     * Default clinic admin seeded by RolePermissionSeeder (non-production fallback: password when allow_insecure).
     */
    'admin_email' => env('SEED_ADMIN_EMAIL', 'admin@clinic.local'),
    'admin_password' => env('SEED_ADMIN_PASSWORD'),

];
