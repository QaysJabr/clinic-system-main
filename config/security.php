<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two-factor authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        /** Master switch — set true when you are ready to enforce 2FA in production */
        'enabled' => (bool) env('SECURITY_2FA_ENABLED', false),
        'issuer' => env('SECURITY_2FA_ISSUER', env('APP_NAME', 'Clinic System')),
        'recovery_codes' => (int) env('SECURITY_2FA_RECOVERY_CODES', 8),
        'trusted_device_days' => (int) env('SECURITY_TRUSTED_DEVICE_DAYS', 30),
        /** Roles that must enable 2FA when clinic setting require_two_factor is on */
        'enforce_roles' => array_filter(array_map('trim', explode(',', (string) env('SECURITY_2FA_ENFORCE_ROLES', 'admin,clinic_owner')))),
        /** Platform super_admin must use 2FA when enabled + enforce_platform_owner */
        'enforce_platform_owner' => (bool) env('SECURITY_2FA_ENFORCE_PLATFORM_OWNER', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email verification (MustVerifyEmail + verified middleware)
    |--------------------------------------------------------------------------
    */
    'email_verification' => [
        'enabled' => (bool) env('EMAIL_VERIFICATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient portal / QR lookup tokens
    |--------------------------------------------------------------------------
    */
    'patient_portal' => [
        'token_ttl_days' => (int) env('PATIENT_PORTAL_TOKEN_TTL_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    'csp' => [
        'enabled' => (bool) env('SECURITY_CSP_ENABLED', true),
        'report_only' => (bool) env('SECURITY_CSP_REPORT_ONLY', false),
        'allow_vite_dev' => (bool) env('SECURITY_CSP_ALLOW_VITE_DEV', true),
        /** Alpine.js compiles x-data expressions — requires unsafe-eval unless using @alpinejs/csp build */
        'allow_unsafe_eval' => (bool) env('SECURITY_CSP_ALLOW_UNSAFE_EVAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload hardening
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'attachments_disk' => env('SECURITY_ATTACHMENTS_DISK', 'local'),
        'max_kb' => (int) env('SECURITY_UPLOAD_MAX_KB', 10240),
        'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
        /** Hook for external malware scanning (class implementing MalwareScanner contract) */
        'malware_scanner' => env('SECURITY_MALWARE_SCANNER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Suspicious login detection
    |--------------------------------------------------------------------------
    */
    'login' => [
        'failed_threshold' => (int) env('SECURITY_LOGIN_FAILED_THRESHOLD', 5),
        'failed_window_minutes' => (int) env('SECURITY_LOGIN_FAILED_WINDOW', 15),
        'new_ip_alert' => (bool) env('SECURITY_LOGIN_NEW_IP_ALERT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Observability
    |--------------------------------------------------------------------------
    */
    'health' => [
        'token' => env('HEALTH_CHECK_TOKEN'),
    ],

];
