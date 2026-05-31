<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription expiry reminders (Phase 2 billing)
    |--------------------------------------------------------------------------
    */
    'subscription_reminders' => [
        'enabled' => filter_var(env('SAAS_SUBSCRIPTION_REMINDERS', true), FILTER_VALIDATE_BOOL),
        'days_before' => array_map('intval', explode(',', (string) env('SAAS_SUBSCRIPTION_REMINDER_DAYS', '7,3'))),
        'email' => filter_var(env('SAAS_SUBSCRIPTION_REMINDER_EMAIL', true), FILTER_VALIDATE_BOOL),
        'in_app' => filter_var(env('SAAS_SUBSCRIPTION_REMINDER_IN_APP', true), FILTER_VALIDATE_BOOL),
    ],

    'stripe_invoices_limit' => (int) env('SAAS_STRIPE_INVOICES_LIMIT', 12),

    'checkout' => [
        'allow_promotion_codes' => filter_var(env('SAAS_ALLOW_PROMOTION_CODES', true), FILTER_VALIDATE_BOOL),
    ],

    'support' => [
        'email' => env('SAAS_SUPPORT_EMAIL', ''),
        'whatsapp' => env('SAAS_SUPPORT_WHATSAPP', '972597360027'),
        'phone' => env('SAAS_SUPPORT_PHONE', ''),
    ],

    'webhook_log' => [
        'enabled' => filter_var(env('SAAS_WEBHOOK_LOG_ENABLED', true), FILTER_VALIDATE_BOOL),
        'keep_per_clinic' => (int) env('SAAS_WEBHOOK_LOG_KEEP', 30),
        'display_limit' => (int) env('SAAS_WEBHOOK_LOG_DISPLAY', 8),
    ],

];
