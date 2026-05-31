<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\NotificationResource;
use App\Models\AppNotification;
use App\Support\AppNotificationType;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlatformNotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $paginator = AppNotification::query()
            ->forUser($request->user()->id)
            ->platformOnly()
            ->orderByDesc('created_at')
            ->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        $unread = AppNotification::query()
            ->forUser($request->user()->id)
            ->platformOnly()
            ->unread()
            ->count();

        return $this->ok(
            NotificationResource::collection($paginator->items()),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'unread_count' => $unread,
            ],
        );
    }

    public function markRead(Request $request, AppNotification $appNotification): JsonResponse
    {
        $this->authorizeOwnPlatform($request, $appNotification);

        $appNotification->update(['is_read' => true]);

        AuditLogger::log(
            'update',
            'platform_notifications',
            $appNotification->id,
            __('notifications.audit_mark_one'),
            null,
            ['is_read' => true]
        );

        return $this->ok(new NotificationResource($appNotification->fresh()));
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

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
            ['marked_all' => true]
        );

        return $this->message(__('notifications.flash_marked_all_read'));
    }

    private function authorizeOwnPlatform(Request $request, AppNotification $appNotification): void
    {
        if ((int) $appNotification->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if (! AppNotificationType::isPlatformType($appNotification->type)) {
            abort(403);
        }
    }
}
