<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Support\AppNotificationType;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = AppNotification::query()
            ->forUser($request->user()->id)
            ->platformOnly()
            ->select(['id', 'user_id', 'type', 'title', 'message', 'is_read', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $pageTitle = __('platform.notifications_title');

        if ($request->ajax()) {
            return view('platform.notifications.partials.index', compact('notifications', 'pageTitle'));
        }

        return view('platform.notifications.index', compact('notifications', 'pageTitle'));
    }

    public function markAsRead(Request $request, AppNotification $appNotification): RedirectResponse
    {
        $this->authorizeOwnPlatform($request, $appNotification);

        $nid = $appNotification->id;
        $appNotification->update(['is_read' => true]);

        AuditLogger::log(
            'update',
            'platform_notifications',
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
            ->platformOnly()
            ->unread()
            ->count();

        AppNotification::query()
            ->forUser($userId)
            ->platformOnly()
            ->unread()
            ->update(['is_read' => true]);

        AuditLogger::log(
            'update',
            'platform_notifications',
            null,
            __('notifications.audit_mark_all'),
            null,
            ['marked_count' => $count]
        );

        return redirect()->route('platform.notifications.index')->with('success', __('notifications.flash_marked_all_read'));
    }

    private function authorizeOwnPlatform(Request $request, AppNotification $appNotification): void
    {
        if ($appNotification->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! AppNotificationType::isPlatformType($appNotification->type)) {
            abort(403);
        }
    }
}
