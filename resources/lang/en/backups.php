<?php

return [
    'page_title' => 'Backups',
    'subtitle' => 'Create, download, or delete database backup files.',

    'hint_db' => 'Active database: :connection (:driver)',
    'hint_storage' => 'Backup files are stored on the application server. Download copies you need for off-site retention.',
    'hint_schedule' => 'Automatic backup daily at :time — last :days days kept. Ensure Laravel scheduler (cron) is running.',

    'stat_count' => 'Backup files',
    'stat_total_size' => 'Total size on disk',
    'stat_latest' => 'Most recent backup',
    'stat_latest_none' => '—',

    'section_files' => 'Files',

    'empty_title' => 'No backups yet',
    'empty_hint' => 'Create a backup to capture the current database. Downloads are suitable for archiving outside the server.',

    'btn_create' => 'Create backup',

    'col_filename' => 'File name',
    'col_size' => 'Size',
    'col_modified' => 'Last modified',
    'col_actions' => 'Actions',

    'btn_download' => 'Download',
    'btn_delete' => 'Delete',

    'confirm_delete_title' => 'Delete backup',
    'confirm_delete_body' => 'Permanently delete this backup file?',
    'empty' => 'No backups yet. Use the button above to create one.',

    'error_generic' => 'Check database settings and backup tools on the server.',
    'error_pg_dump' => 'Could not connect to PostgreSQL or run the backup tool. Ensure PostgreSQL is running on this machine.',
    'error_mysql' => 'Could not run the MySQL backup tool. Check your database settings.',
    'error_restrict_key' => 'PostgreSQL backup tool failed. Ensure the PostgreSQL client is installed and the database is running.',

    'audit_create' => 'Backup created: :filename',
    'audit_download' => 'Backup downloaded: :filename',
    'audit_delete' => 'Backup deleted: :filename',

    'flash_created' => 'Backup created successfully: :filename',
    'flash_create_failed' => 'Could not create backup. :message',
    'flash_delete_failed' => 'Could not delete the file.',
    'flash_deleted' => 'Backup deleted successfully.',

    'notify_success_title' => 'Backup succeeded',
    'notify_failure_title' => 'Backup failed',
    'notify_success_message' => 'Database backup created (:file) at :time. :pruned old file(s) removed.',
    'notify_failure_message' => 'Scheduled backup failed at :time. Reason: :error',

    'mail_success_subject' => ':app — backup succeeded',
    'mail_failure_subject' => ':app — backup failed',
    'mail_success_heading' => 'Backup succeeded',
    'mail_failure_heading' => 'Alert: backup failed',
    'mail_open_app' => 'Open application',
    'mail_whatsapp_cta' => 'Notify team on WhatsApp',
    'mail_footer' => 'This is an automated message from the backup scheduler.',
];
