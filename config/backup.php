<?php

return [

    /*
    |--------------------------------------------------------------------------
    | مسارات أدوات النسخ الاحتياطي (اختياري)
    |--------------------------------------------------------------------------
    |
    | إذا كانت الأدوات غير موجودة في PATH (شائع على Windows)، حدّد المسار الكامل
    | للملف التنفيذي، مثال:
    | BACKUP_PG_DUMP_PATH="C:\Program Files\PostgreSQL\18\bin\pg_dump.exe"
    |
    */

    'pg_dump_path' => env('BACKUP_PG_DUMP_PATH'),

    /*
    | PostgreSQL 17.6+ adds \restrict to plain dumps. On Windows, random key generation
    | can fail ("could not generate restrict key") — set a fixed alphanumeric key.
    */
    'pg_restrict_key' => env('BACKUP_PG_RESTRICT_KEY') ?: 'clinicbackupkey',

    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH'),

    'sqlite3_path' => env('BACKUP_SQLITE3_PATH'),

    /*
    |--------------------------------------------------------------------------
    | النسخ الاحتياطي التلقائي
    |--------------------------------------------------------------------------
    |
    | يومياً الساعة 02:00 (توقيت التطبيق) مع الاحتفاظ بآخر 14 نسخة.
    | عطّل التشغيل التلقائي: BACKUP_SCHEDULE_ENABLED=false
    |
    */

    'schedule_enabled' => env('BACKUP_SCHEDULE_ENABLED', true),

    'schedule_time' => env('BACKUP_SCHEDULE_TIME', '02:00'),

    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | إشعارات النسخ الاحتياطي (بريد + داخل النظام + رابط واتساب)
    |--------------------------------------------------------------------------
    */

    'notify_enabled' => filter_var(env('BACKUP_NOTIFY_ENABLED', true), FILTER_VALIDATE_BOOL),

    'notify_email' => filter_var(env('BACKUP_NOTIFY_EMAIL', true), FILTER_VALIDATE_BOOL),

    'notify_in_app' => filter_var(env('BACKUP_NOTIFY_IN_APP', true), FILTER_VALIDATE_BOOL),

    'notify_on_success' => filter_var(env('BACKUP_NOTIFY_ON_SUCCESS', true), FILTER_VALIDATE_BOOL),

  /** عناوين مخصّصة (مفصولة بفاصلة) — وإلا دعم SaaS ثم super_admin */
    'notify_emails' => env('BACKUP_NOTIFY_EMAILS', ''),

    /** رقم واتساب لزر تنبيه سريع في البريد (بدون إرسال API) */
    'notify_whatsapp' => env('BACKUP_NOTIFY_WHATSAPP', env('SAAS_SUPPORT_WHATSAPP', '')),

];
