<?php

namespace App\Console\Commands;

use App\Services\BackupNotificationService;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database
                            {--prune-only : حذف النسخ القديمة فقط دون إنشاء نسخة جديدة}
                            {--no-prune : عدم حذف النسخ القديمة بعد الإنشاء}';

    protected $description = 'إنشاء نسخة احتياطية لقاعدة البيانات في storage/app/backups (مع تنظيف تلقائي)';

    public function handle(DatabaseBackupService $service, BackupNotificationService $notifier): int
    {
        $pruneOnly = (bool) $this->option('prune-only');
        $retentionDays = (int) Config::get('backup.retention_days', 14);
        $pruned = 0;

        try {
            if (! $pruneOnly) {
                $filename = $service->createBackup();
                $this->info('تم إنشاء النسخة: '.$filename);
                Log::info('Database backup created', ['file' => $filename]);
            }

            if (! $this->option('no-prune') && $retentionDays > 0) {
                $pruned = $service->pruneOldBackups($retentionDays);
                if ($pruned > 0) {
                    $this->line("تم حذف {$pruned} نسخة أقدم من {$retentionDays} يوماً.");
                    Log::info('Database backups pruned', ['deleted' => $pruned, 'retention_days' => $retentionDays]);
                }
            }

            if (! $pruneOnly && isset($filename)) {
                $notifier->notifySuccess($filename, $pruned);
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            Log::error('Database backup failed', ['message' => $e->getMessage()]);
            $notifier->notifyFailure($e->getMessage());

            return self::FAILURE;
        }
    }
}
