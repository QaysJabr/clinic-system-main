<?php

namespace App\Console\Commands;

use App\TestingLab\TestingLabGuard;
use App\TestingLab\TestingLabReporter;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class LabSmokeCommand extends Command
{
    protected $signature = 'lab:smoke';

    protected $description = 'Run local testing-lab suite (PHPUnit Lab tests) and write reports — local/test DB only';

    public function handle(): int
    {
        try {
            TestingLabGuard::assertSafeToRunCommand();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Testing Lab — local smoke suite');
        $this->line('DB: '.config('database.connections.'.config('database.default').'.database'));
        $this->newLine();

        $reportBase = (string) config('testing-lab.reports_path', storage_path('testing-lab'));
        $junitPath = $reportBase.DIRECTORY_SEPARATOR.'junit-lab.xml';

        if (! is_file(base_path('vendor/bin/phpunit'))) {
            $this->error('PHPUnit not found. Run: composer install');

            return self::FAILURE;
        }

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

        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        $duration = microtime(true) - $start;
        $passed = $process->isSuccessful();

        $reportDir = TestingLabReporter::write([
            'passed' => $passed,
            'exit_code' => $process->getExitCode() ?? 1,
            'duration_seconds' => $duration,
            'output' => $process->getOutput().$process->getErrorOutput(),
            'junit_path' => is_file($junitPath) ? $junitPath : null,
        ]);

        $this->newLine();
        $this->info('Report: '.$reportDir);
        $this->line('Latest: '.$reportBase.DIRECTORY_SEPARATOR.'latest.md');

        return $passed ? self::SUCCESS : self::FAILURE;
    }
}
