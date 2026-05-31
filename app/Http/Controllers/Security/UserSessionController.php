<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\UserSessionService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class UserSessionController extends Controller
{
    public function __construct(
        private readonly UserSessionService $sessions,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $activeSessions = $this->sessions->activeSessionsFor($user, $request->session()->getId());

        $sessionStats = [
            'total' => $activeSessions->count(),
            'others' => $activeSessions->reject(fn (object $s): bool => (bool) $s->is_current)->count(),
        ];

        $pageTitle = __('security.sessions_title');

        $viewData = [
            'sessions' => $activeSessions,
            'sessionStats' => $sessionStats,
            'pageTitle' => $pageTitle,
        ];

        if ($request->ajax()) {
            return view('security.sessions.partials.content', $viewData);
        }

        return view('security.sessions.index', $viewData);
    }

    public function destroy(Request $request, string $sessionId): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        if ($sessionId === $request->session()->getId()) {
            return back()->withErrors(['session' => __('security.cannot_revoke_current')]);
        }

        if ($this->sessions->revoke($sessionId, $user)) {
            AuditLogger::security('session_revoked', 'auth', $user->id, 'إلغاء جلسة: '.$sessionId);
        }

        return back()->with('success', __('security.session_revoked'));
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $count = $this->sessions->revokeOthers($user, $request->session()->getId());

        AuditLogger::security('sessions_revoked_others', 'auth', $user->id, 'إلغاء جلسات أخرى: '.$count);

        return back()->with('success', __('security.sessions_revoked_others', ['count' => $count]));
    }
}
