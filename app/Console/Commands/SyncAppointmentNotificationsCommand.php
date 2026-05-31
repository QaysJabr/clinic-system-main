<?php

namespace App\Console\Commands;

use App\Models\Scopes\TenantScope;
use App\Services\InAppNotificationService;
use Illuminate\Console\Command;

class SyncAppointmentNotificationsCommand extends Command
{
    protected $signature = 'notifications:sync-appointment-reminders';

    protected $description = 'إنشاء إشعارات المواعيد (اليوم وخلال 24 ساعة) للمستخدمين المخولين';

    public function handle(InAppNotificationService $service): int
    {
        $previous = TenantScope::$enabled;
        TenantScope::$enabled = false;
        try {
            $service->syncScheduledAppointmentAlerts();
        } finally {
            TenantScope::$enabled = $previous;
        }

        $this->info('تمت مزامنة إشعارات المواعيد.');

        return self::SUCCESS;
    }
}
