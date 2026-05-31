<?php

namespace App\Jobs;

use App\Models\Scopes\TenantScope;
use App\Services\InAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAppointmentNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue(config('performance.queues.notifications', 'default'));
    }

    public function handle(InAppNotificationService $service): void
    {
        $previous = TenantScope::$enabled;
        TenantScope::$enabled = false;
        try {
            $service->syncScheduledAppointmentAlerts();
        } finally {
            TenantScope::$enabled = $previous;
        }
    }
}
