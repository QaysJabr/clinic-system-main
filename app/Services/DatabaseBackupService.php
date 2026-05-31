<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * نسخ احتياطي لقاعدة البيانات عبر أدوات النظام (pg_dump / mysqldump / sqlite3) أو نسخ ملف SQLite.
 */
final class DatabaseBackupService
{
    public function backupDirectory(): string
    {
        return storage_path('app/backups');
    }

    public function ensureDirectoryExists(): void
    {
        $dir = $this->backupDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * @return list<array{name: string, size: int, modified_at: Carbon}>
     */
    public function listBackups(): array
    {
        $this->ensureDirectoryExists();
        $dir = $this->backupDirectory();
        $items = [];

        if (! is_dir($dir)) {
            return [];
        }

        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (! $this->isAllowedBackupFilename($file)) {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (! is_file($path)) {
                continue;
            }
            $items[] = [
                'name' => $file,
                'size' => filesize($path) ?: 0,
                'modified_at' => Carbon::createFromTimestamp(filemtime($path)),
            ];
        }

        usort($items, fn (array $a, array $b) => strcmp($b['name'], $a['name']));

        return $items;
    }

    /**
     * يحذف النسخ الأقدم من عدد الأيام المحدد.
     *
     * @return int عدد الملفات المحذوفة
     */
    public function pruneOldBackups(?int $retentionDays = null): int
    {
        $days = $retentionDays ?? (int) Config::get('backup.retention_days', 14);
        if ($days < 1) {
            return 0;
        }

        $cutoff = now()->subDays($days);
        $deleted = 0;

        foreach ($this->listBackups() as $item) {
            if ($item['modified_at']->lt($cutoff)) {
                $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$item['name'];
                if (is_file($path) && @unlink($path)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * ينشئ ملف نسخة احتياطية ويعيد اسم الملف فقط (داخل مجلد النسخ).
     *
     * @throws \RuntimeException
     */
    public function createBackup(): string
    {
        $this->ensureDirectoryExists();
        $default = Config::get('database.default');
        $driver = Config::get("database.connections.{$default}.driver");

        return match ($driver) {
            'sqlite' => $this->createSqliteBackup(),
            'pgsql' => $this->createPgsqlBackup($default),
            'mysql', 'mariadb' => $this->createMysqlBackup($default),
            default => throw new \RuntimeException('نوع قاعدة البيانات غير مدعوم للنسخ الاحتياطي: '.$driver),
        };
    }

    public function resolveValidatedPath(string $filename): string
    {
        if (! $this->isAllowedBackupFilename($filename)) {
            abort(404);
        }
        $full = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;
        $realBase = realpath($this->backupDirectory());
        $realFile = realpath($full);
        if ($realBase === false || $realFile === false || ! str_starts_with($realFile, $realBase)) {
            abort(404);
        }
        if (! is_file($realFile)) {
            abort(404);
        }

        return $realFile;
    }

    public function isAllowedBackupFilename(string $filename): bool
    {
        return (bool) preg_match('/^clinic_backup_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}\.(sql|sqlite)$/', $filename);
    }

    private function createSqliteBackup(): string
    {
        $connection = Config::get('database.default');
        $database = Config::get("database.connections.{$connection}.database");
        if ($database === ':memory:') {
            throw new \RuntimeException('قاعدة SQLite في الذاكرة لا تدعم النسخ الاحتياطي.');
        }
        $dbPath = $database;
        if (! str_starts_with($dbPath, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $dbPath)) {
            $dbPath = database_path($dbPath);
        }
        if (! is_file($dbPath)) {
            throw new \RuntimeException('ملف قاعدة SQLite غير موجود.');
        }

        $sqlite3 = $this->resolveBinary('sqlite3', Config::get('backup.sqlite3_path'));
        $baseName = 'clinic_backup_'.now()->format('Y_m_d_H_i_s');

        if ($sqlite3 !== null) {
            $sqlName = $baseName.'.sql';
            $full = $this->backupDirectory().DIRECTORY_SEPARATOR.$sqlName;
            $process = new Process([$sqlite3, $dbPath, '.dump']);
            $process->setTimeout(600);
            try {
                $process->mustRun();
            } catch (Throwable $e) {
                throw new \RuntimeException('فشل تنفيذ sqlite3: '.$e->getMessage(), 0, $e);
            }
            if (file_put_contents($full, $process->getOutput()) === false) {
                throw new \RuntimeException('تعذر كتابة ملف النسخة الاحتياطية.');
            }

            return $sqlName;
        }

        $binName = $baseName.'.sqlite';
        $full = $this->backupDirectory().DIRECTORY_SEPARATOR.$binName;
        if (! @copy($dbPath, $full)) {
            throw new \RuntimeException('تعذر نسخ ملف قاعدة SQLite (ثبّت sqlite3 في PATH لإخراج .sql).');
        }

        return $binName;
    }

    private function createPgsqlBackup(string $connection): string
    {
        $pgDump = $this->resolveBinary('pg_dump', Config::get('backup.pg_dump_path'));
        if ($pgDump === null) {
            throw new \RuntimeException(
                'لم يُعثر على pg_dump. ثبّت عميل PostgreSQL، أو أضف مجلد bin إلى PATH، أو عيّن المسار الكامل في .env: BACKUP_PG_DUMP_PATH'
            );
        }

        $host = Config::get("database.connections.{$connection}.host");
        $port = (string) Config::get("database.connections.{$connection}.port", '5432');
        $database = Config::get("database.connections.{$connection}.database");
        $username = Config::get("database.connections.{$connection}.username");
        $password = (string) Config::get("database.connections.{$connection}.password", '');

        $name = 'clinic_backup_'.now()->format('Y_m_d_H_i_s').'.sql';
        $full = $this->backupDirectory().DIRECTORY_SEPARATOR.$name;

        $restrictKey = $this->resolvePgRestrictKey(Config::get('backup.pg_restrict_key'));

        $command = [
            $pgDump,
            '-h', $host,
            '-p', $port,
            '-U', $username,
            '-d', $database,
            '-F', 'p',
            '--no-owner',
            '--restrict-key='.$restrictKey,
        ];

        try {
            $output = $this->runPgDumpProcess($command, $password);
        } catch (Throwable $first) {
            if (! $this->shouldRetryPgDumpWithoutRestrictKey($first, $command)) {
                throw new \RuntimeException('فشل pg_dump: '.$this->processFailureMessage($first), 0, $first);
            }

            $command = array_values(array_filter(
                $command,
                static fn (string $arg): bool => ! str_starts_with($arg, '--restrict-key='),
            ));

            try {
                $output = $this->runPgDumpProcess($command, $password);
            } catch (Throwable $retry) {
                throw new \RuntimeException('فشل pg_dump: '.$this->processFailureMessage($retry), 0, $retry);
            }
        }

        if ($output === '' || file_put_contents($full, $output) === false) {
            @unlink($full);
            throw new \RuntimeException('فشل pg_dump: لم يُنشأ ملف النسخة الاحتياطية أو الملف فارغ.');
        }

        return $name;
    }

    /**
     * Run pg_dump and return SQL output. Inherits the full PHP process environment
     * (PATH, SystemRoot, etc.) so PostgreSQL client DLLs resolve on Windows.
     *
     * @param  list<string>  $command
     */
    private function runPgDumpProcess(array $command, string $password): string
    {
        $prevPassword = getenv('PGPASSWORD');
        if ($password !== '') {
            putenv('PGPASSWORD='.$password);
        }

        try {
            $process = new Process($command, base_path(), null, null, 600);
            $process->mustRun();

            return $process->getOutput();
        } finally {
            if ($prevPassword === false) {
                putenv('PGPASSWORD');
            } else {
                putenv('PGPASSWORD='.$prevPassword);
            }
        }
    }

    private function resolvePgRestrictKey(mixed $configured): string
    {
        $default = 'clinicbackupkey';

        if (! is_string($configured)) {
            return $default;
        }

        $configured = trim($configured, " \t\n\r\0\x0B\"'");

        if ($configured !== '' && preg_match('/^[A-Za-z0-9]+$/', $configured)) {
            return $configured;
        }

        return $default;
    }

    private function shouldRetryPgDumpWithoutRestrictKey(Throwable $e, array $command): bool
    {
        $usesRestrictKey = array_filter(
            $command,
            static fn (string $arg): bool => str_starts_with($arg, '--restrict-key='),
        ) !== [];

        if (! $usesRestrictKey) {
            return false;
        }

        $message = strtolower($this->processFailureMessage($e));

        return str_contains($message, 'restrict-key')
            || str_contains($message, 'restrict key')
            || str_contains($message, 'could not generate restrict')
            || str_contains($message, 'unrecognized option')
            || str_contains($message, 'unknown option');
    }

    private function processFailureMessage(Throwable $e): string
    {
        if ($e instanceof ProcessFailedException) {
            $process = $e->getProcess();
            $stderr = trim($process->getErrorOutput());
            if ($stderr !== '') {
                return $this->extractPgDumpErrorLine($stderr);
            }
        }

        return $this->extractPgDumpErrorLine($e->getMessage());
    }

    private function extractPgDumpErrorLine(string $output): string
    {
        foreach (preg_split('/\R/', $output) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, 'pg_dump:')) {
                return preg_replace('/^.*pg_dump:\s*/', '', $line) ?? $line;
            }
        }

        return trim($output);
    }

    private function createMysqlBackup(string $connection): string
    {
        $mysqldump = $this->resolveBinary('mysqldump', Config::get('backup.mysqldump_path'));
        if ($mysqldump === null) {
            throw new \RuntimeException(
                'لم يُعثر على mysqldump. ثبّت عميل MySQL، أو أضف الأدوات إلى PATH، أو عيّن BACKUP_MYSQLDUMP_PATH في .env'
            );
        }

        $host = Config::get("database.connections.{$connection}.host");
        $port = (string) Config::get("database.connections.{$connection}.port", '3306');
        $database = Config::get("database.connections.{$connection}.database");
        $username = Config::get("database.connections.{$connection}.username");
        $password = (string) Config::get("database.connections.{$connection}.password", '');

        $name = 'clinic_backup_'.now()->format('Y_m_d_H_i_s').'.sql';
        $full = $this->backupDirectory().DIRECTORY_SEPARATOR.$name;

        $process = new Process([
            $mysqldump,
            '--single-transaction',
            '--no-tablespaces',
            '-h', $host,
            '-P', $port,
            '-u', $username,
            $database,
        ], null, null, null, 600);

        $prevMy = getenv('MYSQL_PWD');
        putenv('MYSQL_PWD='.$password);
        try {
            $process->mustRun();
        } catch (Throwable $e) {
            @unlink($full);
            throw new \RuntimeException('فشل mysqldump: '.$e->getMessage(), 0, $e);
        } finally {
            if ($prevMy === false) {
                putenv('MYSQL_PWD');
            } else {
                putenv('MYSQL_PWD='.$prevMy);
            }
        }

        if (file_put_contents($full, $process->getOutput()) === false) {
            throw new \RuntimeException('تعذر كتابة ملف النسخة الاحتياطية.');
        }

        return $name;
    }

    /**
     * يبحث عن الأداة في المسار المعرّف في الإعدادات ثم في PATH.
     */
    private function resolveBinary(string $name, ?string $configuredPath): ?string
    {
        if (is_string($configuredPath) && $configuredPath !== '') {
            $configuredPath = trim($configuredPath, " \t\n\r\0\x0B\"'");
            if ($configuredPath !== '' && is_file($configuredPath)) {
                return $configuredPath;
            }
        }

        $found = (new ExecutableFinder)->find($name);
        if ($found !== null) {
            return $found;
        }

        return $this->discoverWindowsBinary($name);
    }

    private function discoverWindowsBinary(string $name): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $patterns = match ($name) {
            'pg_dump' => [
                'C:\\Program Files\\PostgreSQL\\*\\bin\\pg_dump.exe',
                'C:\\Program Files (x86)\\PostgreSQL\\*\\bin\\pg_dump.exe',
            ],
            'mysqldump' => [
                'C:\\Program Files\\MySQL\\*\\bin\\mysqldump.exe',
                'C:\\Program Files\\MariaDB*\\bin\\mysqldump.exe',
            ],
            'sqlite3' => [
                'C:\\Program Files\\SQLite\\*\\sqlite3.exe',
            ],
            default => [],
        };

        $candidates = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $path) {
                $candidates[] = $path;
            }
        }

        if ($candidates === []) {
            return null;
        }

        rsort($candidates);

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
