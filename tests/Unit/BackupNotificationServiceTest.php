<?php

namespace Tests\Unit;

use App\Mail\DatabaseBackupResultMail;
use App\Services\BackupNotificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackupNotificationServiceTest extends TestCase
{
    public function test_sends_success_mail_to_configured_addresses(): void
    {
        Mail::fake();

        Config::set('backup.notify_emails', 'ops@clinic.test');
        Config::set('backup.notify_whatsapp', '972500000000');

        app(BackupNotificationService::class)->notifySuccess('clinic_backup_2026_05_29_02_00_00.sql', 2);

        Mail::assertSent(DatabaseBackupResultMail::class, function (DatabaseBackupResultMail $mail) {
            return $mail->hasTo('ops@clinic.test')
                && $mail->success === true
                && $mail->whatsappUrl !== null;
        });
    }

    public function test_sends_failure_mail(): void
    {
        Mail::fake();

        Config::set('backup.notify_emails', 'ops@clinic.test');

        app(BackupNotificationService::class)->notifyFailure('pg_dump missing');

        Mail::assertSent(DatabaseBackupResultMail::class, function (DatabaseBackupResultMail $mail) {
            return $mail->success === false;
        });
    }
}
