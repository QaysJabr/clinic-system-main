<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Support\AppNotificationType;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = AppNotification::query()
            ->forUser($request->user()->id)
            ->clinicOnly()
            ->select(['id', 'user_id', 'type', 'title', 'message', 'is_read', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $pageTitle = __('notifications.title');

        if ($request->ajax()) {
            return view('notifications.partials.index', compact('notifications', 'pageTitle'));
        }

        return view('notifications.index', compact('notifications', 'pageTitle'));
    }

    public function markAsRead(Request $request, AppNotification $appNotification): RedirectResponse
    {
        $this->authorizeOwn($request, $appNotification);

        $nid = $appNotification->id;
        $appNotification->update(['is_read' => true]);

        AuditLogger::log(
            'update',
            'notifications',
            $nid,
            __('notifications.audit_mark_one'),
            null,
            ['is_read' => true]
        );

        return redirect()->back()->with('success', __('notifications.flash_marked_read'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $count = AppNotification::query()
            ->forUser($userId)
            ->unread()
            ->count();

        AppNotification::query()
            ->forUser($userId)
            ->unread()
            ->update(['is_read' => true]);

        AuditLogger::log(
            'update',
            'notifications',
            null,
            __('notifications.audit_mark_all'),
            null,
            ['marked_count' => $count]
        );

        return redirect()->route('notifications.index')->with('success', __('notifications.flash_marked_all_read'));
    }

    private function authorizeOwn(Request $request, AppNotification $appNotification): void
    {
        if ($appNotification->user_id !== $request->user()->id) {
            abort(403);
        }

        if (AppNotificationType::isPlatformType($appNotification->type)) {
            abort(403);
        }
    }
}
