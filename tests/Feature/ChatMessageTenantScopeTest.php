<?php

namespace Tests\Feature;

use App\Enums\ChatRoomType;
use App\Models\ChatMessage;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChatMessageTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_cannot_resolve_chat_message_from_another_clinic(): void
    {
        $clinicB = Clinic::query()->create([
            'name' => 'عيادة ب',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => null,
        ]);

        $adminA = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $foreignMessage = ChatMessage::withoutGlobalScopes()->create([
            'clinic_id' => $clinicB->id,
            'sender_id' => $adminA->id,
            'receiver_id' => null,
            'room_type' => ChatRoomType::General,
            'body' => 'رسالة عيادة أخرى',
        ]);

        $intruder = User::factory()->create([
            'email' => 'intruder-chat-scope@test.local',
            'clinic_id' => $adminA->clinic_id,
        ]);
        $intruder->assignRole(Role::findByName('admin'));

        $this->actingAs($intruder)
            ->deleteJson(route('chat.messages.destroy', ['chatMessage' => $foreignMessage->id]))
            ->assertNotFound();
    }
}
