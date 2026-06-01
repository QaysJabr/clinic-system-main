<?php

namespace App\TestingLab;

use Illuminate\Support\Facades\File;

final class TestingLabReporter
{
    /**
     * @param  array{passed: bool, exit_code: int, duration_seconds: float, output: string, junit_path: ?string}  $run
     */
    public static function write(array $run): string
    {
        $base = (string) config('testing-lab.reports_path', storage_path('testing-lab'));
        $runId = now()->format('Y-m-d_His');
        $dir = $base.DIRECTORY_SEPARATOR.$runId;
        File::ensureDirectoryExists($dir);

        $stats = self::parseJUnit($run['junit_path'] ?? null);
        $score = self::score($stats, $run['passed']);

        $payload = [
            'run_id' => $runId,
            'finished_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'database' => 'clinic_test_db (via phpunit.xml)',
            'passed' => $run['passed'],
            'exit_code' => $run['exit_code'],
            'duration_seconds' => round($run['duration_seconds'], 2),
            'tests' => $stats['tests'],
            'failures' => $stats['failures'],
            'errors' => $stats['errors'],
            'score' => $score,
            'verdict' => $run['passed'] ? 'PASS' : 'FAIL',
        ];

        File::put($dir.'/report.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $markdown = self::markdown($payload, $run['output']);
        File::put($dir.'/summary.md', $markdown);
        File::put($base.DIRECTORY_SEPARATOR.'latest.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put($base.DIRECTORY_SEPARATOR.'latest.md', $markdown);

        return $dir;
    }

    /**
     * @return array{tests: int, failures: int, errors: int}
     */
    private static function parseJUnit(?string $path): array
    {
        $empty = ['tests' => 0, 'failures' => 0, 'errors' => 0];

        if ($path === null || ! is_file($path)) {
            return $empty;
        }

        $xml = @simplexml_load_file($path);
        if ($xml === false) {
            return $empty;
        }

        $suite = $xml->testsuite ?? $xml;

        return [
            'tests' => (int) ($suite['tests'] ?? 0),
            'failures' => (int) ($suite['failures'] ?? 0),
            'errors' => (int) ($suite['errors'] ?? 0),
        ];
    }

    /**
     * @param  array{tests: int, failures: int, errors: int}  $stats
     */
    private static function score(array $stats, bool $passed): int
    {
        if (! $passed) {
            return max(0, 100 - ($stats['failures'] + $stats['errors']) * 10);
        }

        return $stats['tests'] > 0 ? 100 : 0;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function markdown(array $payload, string $output): string
    {
        $lines = [
            '# Testing Lab — Smoke Report',
            '',
            '| Field | Value |',
            '|-------|-------|',
            '| Run ID | `'.$payload['run_id'].'` |',
            '| Finished | '.$payload['finished_at'].' |',
            '| Environment | `'.$payload['environment'].'` |',
            '| Database | `'.$payload['database'].'` |',
            '| Tests | '.$payload['tests'].' |',
            '| Failures | '.$payload['failures'].' |',
            '| Errors | '.$payload['errors'].' |',
            '| Score | **'.$payload['score'].'/100** |',
            '| Verdict | **'.$payload['verdict'].'** |',
            '| Duration | '.$payload['duration_seconds'].'s |',
            '',
            '## PHPUnit output (tail)',
            '',
            '```',
            mb_substr(trim($output), -4000),
            '```',
            '',
        ];

        return implode("\n", $lines);
    }
}
