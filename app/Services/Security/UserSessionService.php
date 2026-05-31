<?php

namespace App\Services\Security;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class UserSessionService
{
    /**
     * @return Collection<int, object>
     */
    public function activeSessionsFor(User $user, ?string $currentSessionId = null): Collection
    {
        if (! Schema::hasTable('sessions')) {
            return collect();
        }

        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function (object $row) use ($currentSessionId) {
                $row->is_current = $currentSessionId !== null && $row->id === $currentSessionId;
                $row->device_name = app(TwoFactorService::class)->deviceNameFromAgent($row->user_agent ?? null);
                $row->last_active_at = Carbon::createFromTimestamp((int) $row->last_activity);

                return $row;
            });
    }

    public function revoke(string $sessionId, User $user): bool
    {
        if (! Schema::hasTable('sessions')) {
            return false;
        }

        return DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    public function revokeOthers(User $user, string $exceptSessionId): int
    {
        if (! Schema::hasTable('sessions')) {
            return 0;
        }

        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $exceptSessionId)
            ->delete();
    }
}
