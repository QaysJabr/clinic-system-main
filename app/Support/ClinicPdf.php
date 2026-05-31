<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\Browsershot\Browsershot;
use Throwable;

/**
 * Clinic PDF export: Headless Chrome/Edge loads the real print page (best Arabic),
 * then dedicated export HTML, then DomPDF only if explicitly allowed.
 */
final class ClinicPdf
{
    private static ?string $lastBrowsershotError = null;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function download(string $printView, array $data, string $filename): Response
    {
        self::extendExecutionLimit();

        view()->share('clinicPdfExport', false);
        self::$lastBrowsershotError = null;

        $driver = (string) config('pdf.driver', 'auto');
        $useBrowsershot = in_array($driver, ['browsershot', 'auto'], true);

        if ($useBrowsershot && self::canUseBrowsershot()) {
            // Dedicated export HTML (no HTTP — safe with php artisan serve).
            $exportView = self::resolveBrowsershotView($printView);
            if ($exportView !== null) {
                try {
                    return self::downloadViaBrowsershotHtml($exportView, $data, $filename);
                } catch (Throwable $e) {
                    self::$lastBrowsershotError = $e->getMessage();
                    Log::warning('Browsershot export-html PDF failed.', [
                        'view' => $printView,
                        'export_view' => $exportView,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            // Print URL needs a multi-worker web server; artisan serve deadlocks on self-requests.
            if (self::shouldUsePrintUrl()) {
                $printUrl = self::resolvePrintUrl($printView, $data);
                if ($printUrl !== null) {
                    try {
                        return self::downloadViaPrintUrl($printUrl, $filename);
                    } catch (Throwable $e) {
                        self::$lastBrowsershotError = $e->getMessage();
                        Log::warning('Browsershot print-url PDF failed.', [
                            'view' => $printView,
                            'url' => $printUrl,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            }

            if ($driver === 'browsershot' || ! config('pdf.allow_dompdf_fallback', false)) {
                $detail = self::$lastBrowsershotError ?? self::browsershotRequirementsMessage();
                throw new RuntimeException(__('pdf.browsershot_failed').' '.$detail);
            }
        }

        return self::downloadViaDompdf($printView, $data, $filename);
    }

    private static function shouldUsePrintUrl(): bool
    {
        return (bool) config('pdf.use_print_url', false);
    }

    private static function downloadViaPrintUrl(string $url, string $filename): Response
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '127.0.0.1';

        $shot = self::configureBrowsershot(Browsershot::url($url));

        if (request()) {
            $cookies = request()->cookies->all();
            if ($cookies !== []) {
                $shot->useCookies($cookies, $host);
            }
        }

        $shot->setDelay((int) config('pdf.browsershot_delay_ms', 800));

        $binary = $shot->pdf();

        return self::pdfResponse($binary, $filename);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function downloadViaBrowsershotHtml(string $exportView, array $data, string $filename): Response
    {
        $html = view($exportView, $data)->render();

        $shot = self::configureBrowsershot(Browsershot::html($html));
        $shot->setDelay((int) config('pdf.browsershot_delay_ms', 800));

        $binary = $shot->pdf();

        return self::pdfResponse($binary, $filename);
    }

    private static function configureBrowsershot(Browsershot $shot): Browsershot
    {
        $chrome = self::resolveChromePath();
        $node = self::resolveNodePath();

        if ($chrome === null || $node === null) {
            throw new RuntimeException(self::browsershotRequirementsMessage());
        }

        $nodeModules = base_path('node_modules');

        $protocolSeconds = (int) config('pdf.browsershot_protocol_timeout', 120);

        $shot->showBackground()
            ->emulateMedia('print')
            ->format('A4')
            ->margins(12, 12, 12, 12)
            ->timeout((int) config('pdf.browsershot_timeout', 120))
            ->protocolTimeout($protocolSeconds)
            ->setChromePath($chrome)
            ->setNodeBinary($node)
            ->setNodeModulePath($nodeModules)
            ->setEnvironmentOptions([
                'NODE_PATH' => $nodeModules,
                'LOCALAPPDATA' => getenv('LOCALAPPDATA') ?: '',
                'USERPROFILE' => getenv('USERPROFILE') ?: '',
                'SystemRoot' => getenv('SystemRoot') ?: '',
                'Path' => getenv('Path') ?: '',
            ]);

        if ($npm = config('pdf.npm_path')) {
            $shot->setNpmBinary($npm);
        }

        if (config('pdf.no_sandbox', true)) {
            $shot->noSandbox();
        }

        return $shot;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function downloadViaDompdf(string $printView, array $data, string $filename): Response
    {
        $dompdfView = (string) (config('pdf.dompdf_views.'.$printView) ?? $printView);

        $data['clinicPdfExport'] = true;
        view()->share('clinicPdfExport', true);

        return Pdf::loadView($dompdfView, $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolvePrintUrl(string $printView, array $data): ?string
    {
        if (! request()) {
            return null;
        }

        $query = request()->query();

        $url = match ($printView) {
            'reports.print' => route('reports.print', $query),
            'invoices.print' => isset($data['invoice'])
                ? route('invoices.print', ['invoice' => $data['invoice']])
                : null,
            'expenses.print' => isset($data['expense'])
                ? route('expenses.print', ['expense' => $data['expense']])
                : null,
            'expenses.print-payment' => (isset($data['expense'], $data['payment']))
                ? route('expenses.payment-print', [
                    'expense' => $data['expense'],
                    'expense_payment' => $data['payment'],
                ])
                : null,
            'patients.statement-print' => isset($data['patient'])
                ? route('patients.statement.print', array_merge(['patient' => $data['patient']], $query))
                : null,
            'patients.profile-print' => isset($data['patient'])
                ? route('patients.profile.print', array_merge(['patient' => $data['patient']], $query))
                : null,
            default => null,
        };

        return is_string($url) && $url !== '' ? $url : null;
    }

    public static function resolveBrowsershotView(string $printView): ?string
    {
        $mapped = config('pdf.browsershot_export_views.'.$printView);
        if (is_string($mapped) && $mapped !== '' && view()->exists($mapped)) {
            return $mapped;
        }

        return null;
    }

    public static function canUseBrowsershot(): bool
    {
        return self::resolveChromePath() !== null
            && self::resolveNodePath() !== null
            && is_dir(base_path('node_modules/puppeteer'));
    }

    public static function browsershotRequirementsMessage(): string
    {
        $parts = [];
        if (self::resolveChromePath() === null) {
            $parts[] = 'Chrome/Edge not found (set PDF_CHROME_PATH in .env).';
        }
        if (self::resolveNodePath() === null) {
            $parts[] = 'Node.js not found (set PDF_NODE_PATH in .env).';
        }
        if (! is_dir(base_path('node_modules/puppeteer'))) {
            $parts[] = 'Run: npm install puppeteer';
        }

        return implode(' ', $parts);
    }

    public static function resolveChromePath(): ?string
    {
        $configured = config('pdf.chrome_path');
        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        $candidates = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
                'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            ]
            : [
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
                '/usr/bin/google-chrome-stable',
                '/usr/bin/google-chrome',
                '/snap/bin/chromium',
            ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function resolveNodePath(): ?string
    {
        $configured = config('pdf.node_path');
        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        $candidates = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\nodejs\\node.exe',
                getenv('LOCALAPPDATA').'\\Programs\\nodejs\\node.exe',
                getenv('APPDATA').'\\nvm\\current\\node.exe',
            ]
            : [
                '/usr/bin/node',
                '/usr/local/bin/node',
            ];

        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        $which = PHP_OS_FAMILY === 'Windows' ? 'where node 2>nul' : 'which node 2>/dev/null';
        $found = trim((string) shell_exec($which));
        $first = explode("\n", $found)[0] ?? '';

        return ($first !== '' && is_file($first)) ? $first : null;
    }

    private static function pdfResponse(string $binary, string $filename): Response
    {
        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** Browsershot + Edge cold start can exceed PHP's default 30s limit. */
    private static function extendExecutionLimit(): void
    {
        $seconds = max(60, (int) config('pdf.php_time_limit', 180));
        @ini_set('max_execution_time', (string) $seconds);
        @set_time_limit($seconds);
    }
}
