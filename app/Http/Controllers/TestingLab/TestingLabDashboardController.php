<?php

namespace App\Http\Controllers\TestingLab;

use App\Http\Controllers\Controller;
use App\TestingLab\TestingLabReporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

final class TestingLabDashboardController extends Controller
{
    public function index(): View
    {
        $base = (string) config('testing-lab.reports_path', storage_path('testing-lab'));
        $latest = $this->readJson($base.DIRECTORY_SEPARATOR.'latest.json');
        $runs = $this->listRuns($base);

        return view('testing-lab.index', [
            'pageTitle' => 'Testing Lab',
            'latest' => $latest,
            'runs' => $runs,
            'canRun' => config('testing-lab.dashboard_allow_run', true),
        ]);
    }

    public function show(string $runId): View
    {
        $base = (string) config('testing-lab.reports_path', storage_path('testing-lab'));
        $dir = $base.DIRECTORY_SEPARATOR.$runId;

        if (! is_dir($dir)) {
            abort(404);
        }

        $report = $this->readJson($dir.DIRECTORY_SEPARATOR.'report.json');
        $summaryMd = is_file($dir.DIRECTORY_SEPARATOR.'summary.md')
            ? (string) file_get_contents($dir.DIRECTORY_SEPARATOR.'summary.md')
            : '';

        return view('testing-lab.show', [
            'pageTitle' => 'Testing Lab — '.$runId,
            'runId' => $runId,
            'report' => $report,
            'summaryMd' => $summaryMd,
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        if (! config('testing-lab.dashboard_allow_run', true)) {
            return redirect()->route('testing-lab.index')
                ->with('error', 'تشغيل الاختبارات من الويب معطّل في الإعدادات.');
        }

        if (! is_file(base_path('vendor/bin/phpunit'))) {
            return redirect()->route('testing-lab.index')
                ->with('error', 'PHPUnit غير مثبت. نفّذ: composer install');
        }

        $reportBase = (string) config('testing-lab.reports_path', storage_path('testing-lab'));
        $junitPath = $reportBase.DIRECTORY_SEPARATOR.'junit-lab.xml';
        File::ensureDirectoryExists($reportBase);

        $start = microtime(true);

        $process = new Process(
            [
                PHP_BINARY,
                base_path('vendor/bin/phpunit'),
                '--testsuite',
                'Lab',
                '--log-junit',
                $junitPath,
            ],
            base_path(),
            null,
            null,
            600,
        );
        $process->run();

        $reportDir = TestingLabReporter::write([
            'passed' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode() ?? 1,
            'duration_seconds' => microtime(true) - $start,
            'output' => $process->getOutput().$process->getErrorOutput(),
            'junit_path' => is_file($junitPath) ? $junitPath : null,
        ]);

        $flash = $process->isSuccessful()
            ? 'اكتملت الاختبارات بنجاح.'
            : 'فشلت بعض الاختبارات — راجع التقرير.';

        return redirect()
            ->route('testing-lab.show', ['runId' => basename($reportDir)])
            ->with($process->isSuccessful() ? 'success' : 'error', $flash);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : null;
    }

    /**
     * @return list<array{ id: string, report: array<string, mixed>|null }>
     */
    private function listRuns(string $base): array
    {
        if (! is_dir($base)) {
            return [];
        }

        $runs = [];
        foreach (File::directories($base) as $dir) {
            $id = basename($dir);
            if ($id === '' || $id === 'latest.json') {
                continue;
            }
            $runs[] = [
                'id' => $id,
                'report' => $this->readJson($dir.DIRECTORY_SEPARATOR.'report.json'),
            ];
        }

        usort($runs, fn (array $a, array $b): int => strcmp($b['id'], $a['id']));

        return array_slice($runs, 0, 20);
    }
}
