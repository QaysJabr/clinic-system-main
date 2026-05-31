<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps sessions.user_id in sync for authenticated users (database session driver).
 */
final class TrackActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if ($user === null || ! Schema::hasTable('sessions')) {
            return $response;
        }

        $sessionId = $request->session()->getId();
        if ($sessionId === '') {
            return $response;
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->update([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity' => now()->getTimestamp(),
            ]);

        return $response;
    }
}
