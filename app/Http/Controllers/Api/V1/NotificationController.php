<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $paginator = AppNotification::query()
            ->forUser($request->user()->id)
            ->clinicOnly()
            ->orderByDesc('created_at')
            ->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        $unread = AppNotification::query()
            ->forUser($request->user()->id)
            ->clinicOnly()
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
        if ((int) $appNotification->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $appNotification->update(['is_read' => true]);

        return $this->ok(new NotificationResource($appNotification->fresh()));
    }
}
