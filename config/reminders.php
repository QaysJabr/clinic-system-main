<?php

return [

    'sms' => [
        'enabled' => filter_var(env('REMINDERS_SMS_ENABLED', false), FILTER_VALIDATE_BOOL),
        /** log = local/dev only; twilio = production SMS */
        'driver' => env('REMINDERS_SMS_DRIVER', 'log'),
        'default_country_code' => preg_replace('/\D+/', '', (string) env('REMINDERS_SMS_DEFAULT_COUNTRY', '972')) ?: '972',
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM_NUMBER'),
        ],
    ],

];
