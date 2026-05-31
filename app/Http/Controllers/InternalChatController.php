<?php

namespace App\Http\Controllers;

use App\Enums\ChatRoomType;
use App\Http\Requests\SendGeneralChatMessageRequest;
use App\Http\Requests\SendPrivateChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Models\Appointment;
use App\Models\ChatMessage;
use App\Models\ChatMessageHide;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InternalChatController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $auth = $this->requireClinicUser($request);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = isset($validated['q']) ? trim((string) $validated['q']) : '';

        $unreadBySender = ChatMessage::query()
            ->selectRaw('sender_id, COUNT(*) as c')
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::Private)
            ->where('receiver_id', $auth->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->whereDoesntHave('hides', fn ($q) => $q->where('user_id', $auth->id))
            ->groupBy('sender_id')
            ->pluck('c', 'sender_id');

        $query = User::query()
            ->withoutGlobalScopes()
            ->where('clinic_id', $auth->clinic_id)
            ->whereKeyNot($auth->id)
            ->with('roles:id,name')
            ->orderBy('name');

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $collection = $query->limit(80)->get();
        $peerIds = $collection->pluck('id')->map(fn ($id) => (int) $id)->all();
        $lastByPeer = $this->lastPrivateMessageByPeer($auth, $peerIds);

        $users = $collection->map(function (User $u) use ($unreadBySender, $lastByPeer) {
            $last = $lastByPeer[$u->id] ?? null;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_label' => $this->primaryRoleLabel($u),
                'avatar_url' => $u->avatarUrl(),
                'unread_count' => (int) ($unreadBySender[$u->id] ?? 0),
                'last_message_preview' => $last ? $this->previewBody($last->body) : null,
                'last_message_at' => $last?->created_at?->toIso8601String(),
            ];
        });

        return response()->json(['users' => $users]);
    }

    public function clearGeneralConversation(Request $request): JsonResponse
    {
        $auth = $this->requireClinicUser($request);

        abort_unless($auth->hasRole('admin'), Response::HTTP_FORBIDDEN);

        ChatMessage::query()
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::General)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function generalMessages(Request $request): JsonResponse
    {
        $auth = $this->requireClinicUser($request);

        $validated = $request->validate([
            'before' => ['nullable', 'integer', 'min:1'],
            'after' => ['nullable', 'integer', 'min:1'],
        ]);

        $q = ChatMessage::query()
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::General)
            ->whereNull('deleted_at')
            ->with(['sender.roles:id,name', 'patient:id,clinic_id', 'invoice:id,clinic_id', 'appointment:id,clinic_id']);

        $this->applyVisibleToUser($q, $auth);

        $page = $this->paginateMessages($q, $validated, $auth);

        return response()->json([
            'messages' => $page['messages'],
            'has_more' => $page['has_more'],
            'room_label' => __('chat.general_room_label'),
        ]);
    }

    public function sendGeneral(SendGeneralChatMessageRequest $request): JsonResponse
    {
        $auth = $this->requireClinicUser($request);
        $data = $request->validated();
        $this->assertContextBelongsToClinic($auth->clinic_id, $data);

        $message = ChatMessage::query()->create([
            'clinic_id' => $auth->clinic_id,
            'sender_id' => $auth->id,
            'receiver_id' => null,
            'room_type' => ChatRoomType::General,
            'patient_id' => $data['patient_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'body' => $data['body'],
        ]);

        $message->load(['sender.roles:id,name', 'patient:id,clinic_id', 'invoice:id,clinic_id', 'appointment:id,clinic_id']);

        return response()->json([
            'message' => $this->messageToArray($message, $auth),
            'ok' => true,
        ], Response::HTTP_CREATED);
    }

    public function privateMessages(Request $request, User $user): JsonResponse
    {
        $auth = $this->requireClinicUser($request);
        $this->abortUnlessPartner($auth, $user);

        $validated = $request->validate([
            'before' => ['nullable', 'integer', 'min:1'],
            'after' => ['nullable', 'integer', 'min:1'],
        ]);

        $q = $this->privateConversationQuery($auth, $user)
            ->with(['sender.roles:id,name', 'receiver:id,name', 'patient:id,clinic_id', 'invoice:id,clinic_id', 'appointment:id,clinic_id']);

        $this->applyVisibleToUser($q, $auth);

        $page = $this->paginateMessages($q, $validated, $auth);

        return response()->json([
            'messages' => $page['messages'],
            'has_more' => $page['has_more'],
            'partner' => [
                'id' => $user->id,
                'name' => $user->name,
                'role_label' => $this->primaryRoleLabel($user->loadMissing('roles:id,name')),
                'avatar_url' => $user->avatarUrl(),
            ],
        ]);
    }

    public function sendPrivate(SendPrivateChatMessageRequest $request): JsonResponse
    {
        $auth = $this->requireClinicUser($request);
        $data = $request->validated();

        $receiver = User::query()->withoutGlobalScopes()->whereKey($data['receiver_id'])->firstOrFail();
        $this->abortUnlessPartner($auth, $receiver);

        $this->assertContextBelongsToClinic($auth->clinic_id, $data);

        $message = ChatMessage::query()->create([
            'clinic_id' => $auth->clinic_id,
            'sender_id' => $auth->id,
            'receiver_id' => $receiver->id,
            'room_type' => ChatRoomType::Private,
            'patient_id' => $data['patient_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'body' => $data['body'],
        ]);

        $message->load(['sender.roles:id,name', 'patient:id,clinic_id', 'invoice:id,clinic_id', 'appointment:id,clinic_id']);

        return response()->json([
            'message' => $this->messageToArray($message, $auth),
            'ok' => true,
        ], Response::HTTP_CREATED);
    }

    public function clearPrivateConversation(Request $request, User $user): JsonResponse
    {
        $auth = $this->requireClinicUser($request);
        $this->abortUnlessPartner($auth, $user);

        $messageIds = $this->privateConversationBaseQuery($auth, $user)->pluck('id');

        foreach ($messageIds as $messageId) {
            ChatMessageHide::query()->firstOrCreate([
                'chat_message_id' => $messageId,
                'user_id' => $auth->id,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function markPrivateAsRead(Request $request, User $user): JsonResponse
    {
        $auth = $this->requireClinicUser($request);
        $this->abortUnlessPartner($auth, $user);

        ChatMessage::query()
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::Private)
            ->where('receiver_id', $auth->id)
            ->where('sender_id', $user->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function unreadCounts(Request $request): JsonResponse
    {
        $auth = $this->requireClinicUser($request);

        $bySender = ChatMessage::query()
            ->selectRaw('sender_id, COUNT(*) as c')
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::Private)
            ->where('receiver_id', $auth->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->whereDoesntHave('hides', fn ($q) => $q->where('user_id', $auth->id))
            ->groupBy('sender_id')
            ->pluck('c', 'sender_id');

        $total = (int) $bySender->sum();

        return response()->json([
            'total_private_unread' => $total,
            'by_sender_id' => $bySender->map(fn ($c) => (int) $c)->all(),
        ]);
    }

    public function updateMessage(UpdateChatMessageRequest $request, ChatMessage $chatMessage): JsonResponse
    {
        $auth = $this->requireClinicUser($request);
        $this->abortUnlessMessageAccessible($auth, $chatMessage);

        $body = $request->validated('body');
        $chatMessage->forceFill([
            'body' => $body,
            'edited_at' => now(),
        ])->save();

        $chatMessage->load(['sender.roles:id,name', 'patient:id,clinic_id', 'invoice:id,clinic_id', 'appointment:id,clinic_id']);

        return response()->json(['message' => $this->messageToArray($chatMessage, $auth)]);
    }

    public function destroyMessage(Request $request, ChatMessage $chatMessage): JsonResponse
    {
        $auth = $this->requireClinicUser($request);

        if (! $chatMessage->canBeHiddenBy($auth)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $chatMessage->hideFor($auth);

        return response()->json(['ok' => true]);
    }

    private function requireClinicUser(Request $request): User
    {
        $u = $request->user();
        if (! $u instanceof User || $u->clinic_id === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $u;
    }

    private function abortUnlessPartner(User $auth, User $partner): void
    {
        if ((int) $auth->id === (int) $partner->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ((int) $auth->clinic_id !== (int) $partner->clinic_id) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertContextBelongsToClinic(?int $clinicId, array $data): void
    {
        if ($clinicId === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! empty($data['patient_id'])) {
            $ok = Patient::withoutGlobalScopes()
                ->whereKey((int) $data['patient_id'])
                ->where('clinic_id', $clinicId)
                ->exists();
            abort_unless($ok, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! empty($data['invoice_id'])) {
            $ok = Invoice::withoutGlobalScopes()
                ->whereKey((int) $data['invoice_id'])
                ->where('clinic_id', $clinicId)
                ->exists();
            abort_unless($ok, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! empty($data['appointment_id'])) {
            $ok = Appointment::withoutGlobalScopes()
                ->whereKey((int) $data['appointment_id'])
                ->where('clinic_id', $clinicId)
                ->exists();
            abort_unless($ok, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function privateConversationBaseQuery(User $auth, User $partner)
    {
        return ChatMessage::query()
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::Private)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($auth, $partner) {
                $q->where(function ($q2) use ($auth, $partner) {
                    $q2->where('sender_id', $auth->id)->where('receiver_id', $partner->id);
                })->orWhere(function ($q2) use ($auth, $partner) {
                    $q2->where('sender_id', $partner->id)->where('receiver_id', $auth->id);
                });
            });
    }

    private function privateConversationQuery(User $auth, User $partner)
    {
        return $this->privateConversationBaseQuery($auth, $partner);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ChatMessage>  $query
     */
    private function applyVisibleToUser($query, User $auth): void
    {
        $query->whereDoesntHave('hides', fn ($q) => $q->where('user_id', $auth->id));
    }

    private function abortUnlessMessageAccessible(User $auth, ChatMessage $message): void
    {
        if ((int) $message->clinic_id !== (int) $auth->clinic_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($message->room_type === ChatRoomType::General) {
            abort_unless((int) $message->sender_id === (int) $auth->id, Response::HTTP_FORBIDDEN);

            return;
        }

        $ok = ((int) $message->sender_id === (int) $auth->id || (int) $message->receiver_id === (int) $auth->id);
        abort_unless($ok, Response::HTTP_FORBIDDEN);
    }

    /**
     * @return array<string, mixed>
     */
    private function messageToArray(ChatMessage $m, User $viewer): array
    {
        $sender = $m->sender;
        $isMine = (int) $m->sender_id === (int) $viewer->id;

        $context = $this->buildContextPayload($m, $viewer);

        return [
            'id' => $m->id,
            'body' => $m->body,
            'room_type' => $m->room_type->value,
            'created_at' => $m->created_at?->toIso8601String(),
            'edited_at' => $m->edited_at?->toIso8601String(),
            'read_at' => $m->read_at?->toIso8601String(),
            'is_mine' => $isMine,
            'sender' => $sender ? [
                'id' => $sender->id,
                'name' => $sender->name,
                'role_label' => $this->primaryRoleLabel($sender),
                'avatar_url' => $sender->avatarUrl(),
            ] : null,
            'context' => $context,
            'can_edit' => $m->canBeEditedBy($viewer),
            'can_delete' => $m->canBeDeletedBy($viewer),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildContextPayload(ChatMessage $m, User $viewer): ?array
    {
        $clinicId = $viewer->clinic_id;
        if ($m->patient_id) {
            $p = $m->relationLoaded('patient') ? $m->patient : Patient::withoutGlobalScopes()->find($m->patient_id);
            if ($p && (int) $p->clinic_id === (int) $clinicId) {
                return [
                    'type' => 'patient',
                    'label' => __('chat.ctx_patient_message'),
                    'link_label' => __('chat.ctx_patient_link'),
                    'url' => route('patients.show', $p),
                ];
            }
        }
        if ($m->invoice_id) {
            $inv = $m->relationLoaded('invoice') ? $m->invoice : Invoice::withoutGlobalScopes()->find($m->invoice_id);
            if ($inv && (int) $inv->clinic_id === (int) $clinicId) {
                return [
                    'type' => 'invoice',
                    'label' => __('chat.ctx_invoice_message'),
                    'link_label' => __('chat.ctx_invoice_link'),
                    'url' => route('invoices.show', $inv),
                ];
            }
        }
        if ($m->appointment_id) {
            $a = $m->relationLoaded('appointment') ? $m->appointment : Appointment::withoutGlobalScopes()->find($m->appointment_id);
            if ($a && (int) $a->clinic_id === (int) $clinicId) {
                return [
                    'type' => 'appointment',
                    'label' => __('chat.ctx_appointment_message'),
                    'link_label' => __('chat.ctx_appointment_link'),
                    'url' => route('appointments.show', $a),
                ];
            }
        }

        return null;
    }

    private function primaryRoleLabel(User $user): string
    {
        $user->loadMissing('roles');
        $name = $user->roles->pluck('name')->first();

        return match ($name) {
            'admin' => __('chat.role_admin'),
            'doctor' => __('chat.role_doctor'),
            'receptionist' => __('chat.role_receptionist'),
            'accountant' => __('chat.role_accountant'),
            'super_admin' => __('chat.role_super_admin'),
            default => $name ? (string) $name : __('chat.role_unknown'),
        };
    }

    /**
     * @param  array<int>  $peerIds
     * @return array<int, ChatMessage>
     */
    private function lastPrivateMessageByPeer(User $auth, array $peerIds): array
    {
        if ($peerIds === []) {
            return [];
        }

        $recent = ChatMessage::query()
            ->where('clinic_id', $auth->clinic_id)
            ->where('room_type', ChatRoomType::Private)
            ->whereNull('deleted_at')
            ->whereDoesntHave('hides', fn ($q) => $q->where('user_id', $auth->id))
            ->where(function ($q) use ($auth, $peerIds) {
                $q->where(function ($q2) use ($auth, $peerIds) {
                    $q2->where('sender_id', $auth->id)->whereIn('receiver_id', $peerIds);
                })->orWhere(function ($q2) use ($auth, $peerIds) {
                    $q2->where('receiver_id', $auth->id)->whereIn('sender_id', $peerIds);
                });
            })
            ->orderByDesc('id')
            ->limit(400)
            ->get(['id', 'sender_id', 'receiver_id', 'body', 'created_at']);

        $byPeer = [];
        foreach ($recent as $message) {
            $peerId = (int) $message->sender_id === (int) $auth->id
                ? (int) $message->receiver_id
                : (int) $message->sender_id;
            if (! isset($byPeer[$peerId])) {
                $byPeer[$peerId] = $message;
            }
        }

        return $byPeer;
    }

    private function previewBody(?string $body): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $body) ?? '');

        return mb_strlen($text) > 72 ? mb_substr($text, 0, 72).'…' : $text;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{messages: list<array<string, mixed>>, has_more: bool}
     */
    private function paginateMessages($query, array $validated, User $auth): array
    {
        $after = ! empty($validated['after']) ? (int) $validated['after'] : null;
        $before = ! empty($validated['before']) ? (int) $validated['before'] : null;
        $limit = 50;

        if ($after !== null) {
            $messages = (clone $query)->where('id', '>', $after)->orderBy('id')->limit($limit)->get();

            return [
                'messages' => $messages->map(fn (ChatMessage $m) => $this->messageToArray($m, $auth))->all(),
                'has_more' => false,
            ];
        }

        $q = clone $query;
        if ($before !== null) {
            $q->where('id', '<', $before);
        }

        $batch = $q->orderByDesc('id')->limit($limit + 1)->get();
        $hasMore = $batch->count() > $limit;
        if ($hasMore) {
            $batch = $batch->take($limit);
        }

        $ordered = $batch->reverse()->values();

        return [
            'messages' => $ordered->map(fn (ChatMessage $m) => $this->messageToArray($m, $auth))->all(),
            'has_more' => $hasMore,
        ];
    }
}
