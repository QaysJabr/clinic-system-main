<?php

namespace Tests\Unit;

use App\Services\DatabaseBackupService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DatabaseBackupPruneTest extends TestCase
{
    public function test_prune_deletes_backups_older_than_retention_days(): void
    {
        $service = app(DatabaseBackupService::class);
        $dir = $service->backupDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $oldName = 'clinic_backup_2020_01_01_00_00_00.sql';
        $keepName = 'clinic_backup_'.now()->format('Y_m_d_H_i_s').'.sql';
        $oldPath = $dir.DIRECTORY_SEPARATOR.$oldName;
        $keepPath = $dir.DIRECTORY_SEPARATOR.$keepName;

        file_put_contents($oldPath, '-- old');
        file_put_contents($keepPath, '-- new');
        touch($oldPath, Carbon::parse('2020-01-01')->timestamp);
        touch($keepPath, now()->timestamp);

        Config::set('backup.retention_days', 14);

        $deleted = $service->pruneOldBackups(14);

        $this->assertSame(1, $deleted);
        $this->assertFileDoesNotExist($oldPath);
        $this->assertFileExists($keepPath);

        @unlink($keepPath);
    }
}
