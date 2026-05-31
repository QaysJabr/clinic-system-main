<?php

namespace App\Services\Security;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class SuspiciousLoginService
{
    public function recordSuccess(User $user, Request $request): void
    {
        if (! Schema::hasTable('login_attempts')) {
            return;
        }

        try {
            LoginAttempt::query()->create([
                'email' => $user->email,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $this->trimAgent($request->userAgent()),
                'successful' => true,
            ]);
        } catch (\Throwable) {
            return;
        }

        if (! config('security.login.new_ip_alert')) {
            return;
        }

        $knownIp = LoginAttempt::query()
            ->where('user_id', $user->id)
            ->where('successful', true)
            ->where('ip_address', $request->ip())
            ->where('created_at', '<', now()->subMinute())
            ->exists();

        if (! $knownIp) {
            AuditLogger::security(
                'login_new_ip',
                'auth',
                $user->id,
                'تسجيل دخول من عنوان IP جديد: '.$request->ip(),
                null,
                ['ip' => $request->ip()],
            );
        }
    }

    public function recordFailure(string $email, Request $request, ?string $reason = null): void
    {
        if (! Schema::hasTable('login_attempts')) {
            return;
        }

        try {
            LoginAttempt::query()->create([
                'email' => Str::lower(trim($email)),
                'user_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $this->trimAgent($request->userAgent()),
                'successful' => false,
                'failure_reason' => $reason,
            ]);
        } catch (\Throwable) {
            return;
        }

        $threshold = (int) config('security.login.failed_threshold', 5);
        $window = (int) config('security.login.failed_window_minutes', 15);

        try {
            $recentFails = LoginAttempt::query()
                ->where('email', Str::lower(trim($email)))
                ->where('successful', false)
                ->where('created_at', '>=', now()->subMinutes($window))
                ->count();

            if ($recentFails >= $threshold) {
                AuditLogger::security(
                    'login_bruteforce_suspected',
                    'auth',
                    null,
                    'محاولات دخول فاشلة متكررة: '.$email,
                    null,
                    ['email' => $email, 'count' => $recentFails, 'ip' => $request->ip()],
                );
            }
        } catch (\Throwable) {
            // non-blocking
        }
    }

    private function trimAgent(?string $agent): ?string
    {
        if ($agent === null) {
            return null;
        }

        return strlen($agent) > 2000 ? substr($agent, 0, 2000) : $agent;
    }
}
