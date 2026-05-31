<?php

return [

    'redis' => [
        'recommended_in_production' => true,
        'cache_store' => env('CACHE_STORE', 'database'),
        'queue_connection' => env('QUEUE_CONNECTION', 'database'),
        'session_driver' => env('SESSION_DRIVER', 'database'),
    ],

    'cache' => [
        'dashboard_financial_ttl' => (int) env('CACHE_DASHBOARD_FINANCIAL_TTL', 60),
        'dashboard_analytics_ttl' => (int) env('CACHE_DASHBOARD_ANALYTICS_TTL', 120),
        'dashboard_operational_ttl' => (int) env('CACHE_DASHBOARD_OPERATIONAL_TTL', 45),
        'report_data_ttl' => (int) env('CACHE_REPORT_DATA_TTL', 90),
        'doctors_list_ttl' => (int) env('CACHE_DOCTORS_LIST_TTL', 900),
    ],

    'spa' => [
        'fragment_cache_max_entries' => (int) env('SPA_FRAGMENT_CACHE_MAX', 24),
        'fragment_cache_ttl_ms' => (int) env('SPA_FRAGMENT_CACHE_TTL_MS', 300000),
    ],

    'attachments' => [
        'thumbnail_max_width' => (int) env('ATTACHMENT_THUMB_MAX_WIDTH', 480),
        'thumbnail_quality' => (int) env('ATTACHMENT_THUMB_QUALITY', 82),
    ],

    'exports' => [
        // false = instant browser download; true + async queue = background job + notification
        'queue' => filter_var(env('EXPORTS_QUEUE', env('APP_ENV', 'production') !== 'local'), FILTER_VALIDATE_BOOL),
    ],

    'queues' => [
        'default' => env('QUEUE_CONNECTION', 'database'),
        'notifications' => env('QUEUE_NOTIFICATIONS', 'default'),
        'exports' => env('QUEUE_EXPORTS', 'default'),
        'reminders' => env('QUEUE_REMINDERS', 'default'),
    ],

    'horizon' => [
        'path' => env('HORIZON_PATH', 'horizon'),
        'allowed_emails' => array_filter(array_map('trim', explode(',', (string) env('HORIZON_ALLOWED_EMAILS', '')))),
    ],

];
