<?php

namespace App\Services\Exports;

use App\Jobs\ProcessExcelExportJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Queues large Excel exports when a real queue driver is configured.
 */
final class ExportDispatcher
{
    public static function shouldQueue(): bool
    {
        // Local dev: always download immediately (no queue worker required).
        if (app()->environment('local', 'testing')) {
            return false;
        }

        if (! (bool) config('performance.exports.queue', true)) {
            return false;
        }

        return config('queue.default') !== 'sync';
    }

    /**
     * @param  callable(): StreamedResponse  $stream
     */
    public static function excel(Request $request, string $type, string $filename, callable $stream): StreamedResponse|RedirectResponse
    {
        if (! self::shouldQueue()) {
            return $stream();
        }

        ProcessExcelExportJob::dispatch(
            $type,
            (int) $request->user()->id,
            $request->query(),
            $filename,
        )->onQueue(config('performance.queues.exports', 'default'));

        return redirect()
            ->back()
            ->with('success', __('exports.queued'));
    }
}
