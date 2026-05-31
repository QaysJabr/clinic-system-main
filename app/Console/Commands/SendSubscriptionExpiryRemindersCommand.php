<?php

namespace App\Console\Commands;

use App\Services\Saas\SubscriptionExpiryReminderService;
use Illuminate\Console\Command;

final class SendSubscriptionExpiryRemindersCommand extends Command
{
    protected $signature = 'saas:send-subscription-reminders';

    protected $description = 'Send email and in-app reminders before clinic subscription expiry';

    public function handle(SubscriptionExpiryReminderService $reminders): int
    {
        $stats = $reminders->dispatchDueReminders();

        $this->info(sprintf(
            'Reminders sent — email: %d, in-app: %d, skipped inactive: %d',
            $stats['email'],
            $stats['in_app'],
            $stats['skipped'],
        ));

        return self::SUCCESS;
    }
}
