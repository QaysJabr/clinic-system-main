<?php

use App\Jobs\SyncAppointmentNotificationsJob;
use App\Support\ClinicPdf;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clinic:pdf-check', function () {
    $driver = (string) config('pdf.driver', 'auto');
    $this->info('PDF driver: '.$driver);

    $chrome = ClinicPdf::resolveChromePath();
    $node = ClinicPdf::resolveNodePath();

    $this->line('Chrome: '.($chrome ?? 'NOT FOUND'));
    $this->line('Node:   '.($node ?? 'NOT FOUND'));

    if ($chrome === null || $node === null) {
        $this->error('Browsershot unavailable — PDF will use DomPDF (poor Arabic).');
        $this->line('Install: sudo apt install -y chromium-browser nodejs');
        $this->line('.env: PDF_CHROME_PATH=/usr/bin/chromium-browser  PDF_NODE_PATH=/usr/bin/node');

        return 1;
    }

    $this->info('Browsershot environment OK.');

    return 0;
})->purpose('Verify Chrome and Node.js for PDF export (Browsershot)');

Schedule::call(function (): void {
    if (config('queue.default') === 'sync') {
        Artisan::call('notifications:sync-appointment-reminders');

        return;
    }

    SyncAppointmentNotificationsJob::dispatch();
})->hourly()->name('sync-appointment-notifications');

Schedule::command('inventory:scan-alerts')->dailyAt('07:00')->name('scan-inventory-alerts');

Schedule::command('saas:send-subscription-reminders')
    ->dailyAt('08:00')
    ->name('saas-subscription-expiry-reminders');

if (config('backup.schedule_enabled', true)) {
    $backupTime = (string) config('backup.schedule_time', '02:00');
    if (! preg_match('/^\d{2}:\d{2}$/', $backupTime)) {
        $backupTime = '02:00';
    }

    Schedule::command('backup:database')
        ->dailyAt($backupTime)
        ->withoutOverlapping(120)
        ->onOneServer()
        ->name('database-auto-backup');
}
