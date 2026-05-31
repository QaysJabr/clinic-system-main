<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function __construct(
        private readonly DatabaseBackupService $backupService,
    ) {}

    public function index(Request $request): View
    {
        $backups = $this->backupService->listBackups();

        $totalBytes = 0;
        foreach ($backups as $b) {
            $totalBytes += (int) ($b['size'] ?? 0);
        }

        $defaultConnection = (string) config('database.default');
        $dbDriver = (string) config("database.connections.{$defaultConnection}.driver", 'unknown');

        $backupStats = [
            'count' => count($backups),
            'total_bytes' => $totalBytes,
            'latest' => $backups[0]['modified_at'] ?? null,
        ];

        $viewData = [
            'backups' => $backups,
            'backupStats' => $backupStats,
            'dbDriver' => $dbDriver,
            'dbConnection' => $defaultConnection,
            'pageTitle' => __('backups.page_title'),
        ];

        if ($request->ajax()) {
            return view('backups.partials.content', $viewData);
        }

        return view('backups.index', $viewData);
    }

    public function store(): RedirectResponse
    {
        try {
            $filename = $this->backupService->createBackup();
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('backups.index')
                ->with('error', __('backups.flash_create_failed', [
                    'message' => $this->userFacingBackupError($e),
                ]));
        }

        AuditLogger::log(
            'create',
            'backups',
            null,
            __('backups.audit_create', ['filename' => $filename]),
            null,
            ['filename' => $filename],
        );

        return redirect()
            ->route('backups.index')
            ->with('success', __('backups.flash_created', ['filename' => $filename]));
    }

    public function download(string $filename): BinaryFileResponse
    {
        $path = $this->backupService->resolveValidatedPath($filename);

        AuditLogger::log(
            'download',
            'backups',
            null,
            __('backups.audit_download', ['filename' => $filename]),
            null,
            ['filename' => $filename],
        );

        return response()->download($path, $filename, [
            'Content-Type' => str_ends_with($filename, '.sqlite')
                ? 'application/octet-stream'
                : 'application/sql',
        ]);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $path = $this->backupService->resolveValidatedPath($filename);

        if (! @unlink($path)) {
            return redirect()
                ->route('backups.index')
                ->with('error', __('backups.flash_delete_failed'));
        }

        AuditLogger::log(
            'delete',
            'backups',
            null,
            __('backups.audit_delete', ['filename' => $filename]),
            ['filename' => $filename],
            null,
        );

        return redirect()
            ->route('backups.index')
            ->with('success', __('backups.flash_deleted'));
    }

    private function userFacingBackupError(Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'restrict key') || str_contains($message, 'restrict-key')) {
            return __('backups.error_restrict_key');
        }

        if (str_contains($message, 'pg_dump') || str_contains($message, 'postgresql')) {
            return __('backups.error_pg_dump');
        }

        if (str_contains($message, 'mysqldump') || str_contains($message, 'mysql')) {
            return __('backups.error_mysql');
        }

        return __('backups.error_generic');
    }
}
