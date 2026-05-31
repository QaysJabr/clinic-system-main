<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDF engine
    |--------------------------------------------------------------------------
    |
    | browsershot — Headless Chrome/Edge: same layout as print (Arabic + English).
    | dompdf      — Legacy fallback when Chrome and Node are unavailable.
    | auto        — Try browsershot, then dompdf.
    |
    */
    'driver' => env('PDF_DRIVER', 'auto'),

    'chrome_path' => env('PDF_CHROME_PATH'),

    'node_path' => env('PDF_NODE_PATH'),

    'npm_path' => env('PDF_NPM_PATH'),

    'browsershot_timeout' => (int) env('PDF_BROWSERSHOT_TIMEOUT', 120),

    'browsershot_protocol_timeout' => (int) env('PDF_BROWSERSHOT_PROTOCOL_TIMEOUT', 120),

    'browsershot_delay_ms' => (int) env('PDF_BROWSERSHOT_DELAY_MS', 800),

    /** Must be greater than browsershot_timeout (seconds). */
    'php_time_limit' => (int) env('PDF_PHP_TIME_LIMIT', 180),

    'no_sandbox' => env('PDF_NO_SANDBOX', true),

    /**
     * Fetch /reports/print in Headless Chrome. Only on php-fpm/Apache — not artisan serve.
     */
    'use_print_url' => env('PDF_USE_PRINT_URL', false),

    /** DomPDF when Browsershot fails (Noto Sans Arabic under public/fonts if present). */
    'allow_dompdf_fallback' => env('PDF_ALLOW_DOMPDF_FALLBACK', true),

    /*
    |--------------------------------------------------------------------------
    | Print view => Headless Chrome export view (RTL-safe, no DomPDF shaping)
    |--------------------------------------------------------------------------
    */
    'browsershot_export_views' => [
        'reports.print' => 'reports.export',
        'patients.statement-print' => 'patients.statement-export',
        'patients.profile-print' => 'patients.profile-export',
        'invoices.print' => 'invoices.invoice-export',
        'expenses.print' => 'expenses.export',
        'expenses.print-payment' => 'expenses.payment-export',
        'reports.receivables-pdf' => 'reports.receivables-export',
        'reports.doctors.pdf' => 'reports.doctors.export',
    ],

    /*
    |--------------------------------------------------------------------------
    | DomPDF view for each print view (fallback only)
    |--------------------------------------------------------------------------
    */
    'dompdf_views' => [
        'patients.statement-print' => 'patients.statement-pdf',
        'invoices.print' => 'invoices.pdf',
        'patients.profile-print' => 'patients.profile-pdf',
        'expenses.print' => 'expenses.pdf',
        'expenses.print-payment' => 'expenses.pdf-payment',
        'reports.print' => 'reports.pdf',
        'reports.receivables-pdf' => 'reports.receivables-pdf',
        'reports.doctors.pdf' => 'reports.doctors.pdf',
    ],

];
