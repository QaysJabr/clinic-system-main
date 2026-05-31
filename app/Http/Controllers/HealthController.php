<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

final class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) config('security.health.token');
        if ($token !== '' && $request->query('token') !== $token) {
            abort(403);
        }

        $checks = [
            'app' => true,
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'redis' => $this->checkRedis(),
        ];

        $healthy = collect($checks)->every(fn ($v) => $v === true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('users');
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            Cache::put('health_check', '1', 10);

            return Cache::get('health_check') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkQueue(): bool
    {
        try {
            $connection = config('queue.default');

            return $connection !== null && $connection !== '';
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkRedis(): bool
    {
        if (config('cache.default') !== 'redis' && config('queue.default') !== 'redis') {
            return true;
        }

        try {
            Redis::connection()->ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
