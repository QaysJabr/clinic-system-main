<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local testing lab (php artisan lab:smoke)
    |--------------------------------------------------------------------------
    | Runs only on local/staging DB names listed below — never production.
    */

    'allowed_environments' => ['local', 'testing'],

    'allowed_databases' => [
        'clinic_test_db',
        'clinic_system_test',
        'clinic_testing',
    ],

    'reports_path' => storage_path('testing-lab'),

    'dashboard_enabled' => (bool) env('TESTING_LAB_DASHBOARD', true),

    /** Allow POST /testing-lab/run from browser (local only). */
    'dashboard_allow_run' => (bool) env('TESTING_LAB_DASHBOARD_RUN', true),

];
