<?php

namespace Tests\Feature;

use App\Enums\ChatRoomType;
use App\Models\ChatMessage;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InternalChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_send_and_read_general_message(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $doctorRole = Role::findByName('doctor');
        $doc = User::factory()->create([
            'email' => 'doc-chat@test.local',
            'clinic_id' => $admin->clinic_id,
        ]);
        $doc->assignRole($doctorRole);

        $this->actingAs($admin);

        $this->postJson(route('chat.general.send'), [
            'body' => 'مرحباً بالفريق',
        ])->assertCreated();

        $this->actingAs($doc);
        $this->getJson(route('chat.general'))
            ->assertOk()
            ->assertJsonFragment(['body' => 'مرحباً بالفريق']);
    }

    public function test_private_message_unread_and_mark_read(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $recv = User::factory()->create([
            'email' => 'recv-chat@test.local',
            'clinic_id' => $admin->clinic_id,
        ]);
        $recv->assignRole(Role::findByName('receptionist'));

        $this->actingAs($admin);
        $this->postJson(route('chat.private.send'), [
            'receiver_id' => $recv->id,
            'body' => 'رسالة سرية',
        ])->assertCreated();

        $this->actingAs($recv);
        $this->getJson(route('chat.unread-counts'))
            ->assertOk()
            ->assertJsonPath('total_private_unread', 1);

        $this->postJson(route('chat.private.read', $admin))->assertOk();

        $this->getJson(route('chat.unread-counts'))
            ->assertOk()
            ->assertJsonPath('total_private_unread', 0);

        $this->getJson(route('chat.private.messages', $admin))->assertOk();

        $this->assertNotNull(
            ChatMessage::query()
                ->where('room_type', ChatRoomType::Private)
                ->where('sender_id', $admin->id)
                ->where('receiver_id', $recv->id)
                ->value('read_at')
        );
    }

    public function test_doctor_cannot_clear_general_room(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $doctor = User::factory()->create([
            'email' => 'doc-clear-general@test.local',
            'clinic_id' => $admin->clinic_id,
        ]);
        $doctor->assignRole(Role::findByName('doctor'));

        $this->actingAs($admin);
        $this->postJson(route('chat.general.send'), ['body' => 'رسالة عامة'])->assertCreated();

        $this->actingAs($doctor);
        $this->deleteJson(route('chat.general.conversation.clear'))->assertForbidden();
    }

    public function test_users_list_includes_last_private_message_preview(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $peer = User::factory()->create([
            'email' => 'peer-preview@test.local',
            'clinic_id' => $admin->clinic_id,
        ]);
        $peer->assignRole(Role::findByName('doctor'));

        $this->actingAs($admin);
        $this->postJson(route('chat.private.send'), [
            'receiver_id' => $peer->id,
            'body' => 'آخر رسالة للمعاينة',
        ])->assertCreated();

        $response = $this->getJson(route('chat.users'))->assertOk();
        $peerRow = collect($response->json('users'))->firstWhere('id', $peer->id);
        $this->assertNotNull($peerRow);
        $this->assertSame('آخر رسالة للمعاينة', $peerRow['last_message_preview']);
        $this->assertNotEmpty($peerRow['last_message_at']);
        $this->assertNotEmpty($peerRow['avatar_url']);
    }

    public function test_general_messages_after_returns_only_newer(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $this->postJson(route('chat.general.send'), ['body' => 'أولى'])->assertCreated();
        $first = $this->getJson(route('chat.general'))->json('messages.0');
        $this->postJson(route('chat.general.send'), ['body' => 'ثانية'])->assertCreated();

        $this->getJson(route('chat.general', ['after' => $first['id']]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonFragment(['body' => 'ثانية']);
    }

    public function test_general_conversation_clear_soft_deletes_all_general_messages(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);
        $this->postJson(route('chat.general.send'), ['body' => 'واحدة'])->assertCreated();
        $this->postJson(route('chat.general.send'), ['body' => 'اثنتان'])->assertCreated();

        $this->deleteJson(route('chat.general.conversation.clear'))->assertOk();

        $this->getJson(route('chat.general'))
            ->assertOk()
            ->assertJsonPath('messages', []);

        $this->assertSame(
            0,
            ChatMessage::query()
                ->where('room_type', ChatRoomType::General)
                ->where('clinic_id', $admin->clinic_id)
                ->whereNull('deleted_at')
                ->count()
        );
    }

    public function test_private_conversation_clear_hides_messages_for_clearer_only(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $recv = User::factory()->create([
            'email' => 'recv-clear@test.local',
            'clinic_id' => $admin->clinic_id,
        ]);
        $recv->assignRole(Role::findByName('receptionist'));

        $this->actingAs($admin);
        $this->postJson(route('chat.private.send'), [
            'receiver_id' => $recv->id,
            'body' => 'أولى',
        ])->assertCreated();
        $this->postJson(route('chat.private.send'), [
            'receiver_id' => $recv->id,
            'body' => 'ثانية',
        ])->assertCreated();

        $this->deleteJson(route('chat.private.conversation.clear', $recv))->assertOk();

        $this->getJson(route('chat.private.messages', $recv))
            ->assertOk()
            ->assertJsonPath('messages', []);

        $this->actingAs($recv);
        $this->getJson(route('chat.private.messages', $admin))
            ->assertOk()
            ->assertJsonFragment(['body' => 'أولى']);

        $this->assertSame(
            2,
            ChatMessage::query()
                ->where('room_type', ChatRoomType::Private)
                ->whereNull('deleted_at')
                ->count()
        );
    }

    public function test_user_cannot_access_private_thread_from_other_clinic(): void
    {
        $clinicB = Clinic::query()->create([
            'name' => 'عيادة أخرى',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => null,
        ]);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $intruder = User::factory()->create([
            'email' => 'other-clinic-chat@test.local',
            'clinic_id' => $clinicB->id,
        ]);
        $intruder->assignRole(Role::findByName('admin'));

        $this->actingAs($intruder);
        $this->getJson(route('chat.private.messages', $admin))->assertForbidden();
    }

    public function test_context_patient_must_belong_to_same_clinic(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $patient = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $admin->clinic_id,
            'file_number' => 'CHAT-001',
            'full_name' => 'مريض للسياق',
            'status' => 'active',
        ]);

        $this->actingAs($admin);
        $this->postJson(route('chat.general.send'), [
            'body' => 'مع سياق',
            'patient_id' => $patient->id,
        ])->assertCreated()
            ->assertJsonPath('message.context.type', 'patient');

        $clinicB = Clinic::query()->create([
            'name' => 'عيادة ب',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => null,
        ]);
        $foreignPatient = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicB->id,
            'file_number' => 'CHAT-B-001',
            'full_name' => 'مريض أجنبي',
            'status' => 'active',
        ]);

        $this->postJson(route('chat.general.send'), [
            'body' => 'لا يجوز',
            'patient_id' => $foreignPatient->id,
        ])->assertUnprocessable();
    }

    public function test_hide_message_only_for_user_who_deleted(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $peer = User::factory()->create([
            'email' => 'peer-hide@test.local',
            'clinic_id' => $admin->clinic_id,
        ]);
        $peer->assignRole(Role::findByName('doctor'));

        $this->actingAs($admin);
        $res = $this->postJson(route('chat.general.send'), ['body' => 'للحذف']);
        $id = (int) $res->json('message.id');

        $this->deleteJson(route('chat.messages.destroy', ['chatMessage' => $id]))->assertOk();

        $this->getJson(route('chat.general'))
            ->assertOk()
            ->assertJsonPath('messages', []);

        $this->actingAs($peer);
        $this->getJson(route('chat.general'))
            ->assertOk()
            ->assertJsonFragment(['body' => 'للحذف']);

        $this->assertNull(ChatMessage::query()->find($id)?->deleted_at);
    }

    public function test_edit_message_updates_body(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $res = $this->postJson(route('chat.general.send'), ['body' => 'قديم']);
        $id = (int) $res->json('message.id');

        $this->patchJson(route('chat.messages.update', ['chatMessage' => $id]), [
            'body' => 'جديد',
        ])
            ->assertOk()
            ->assertJsonPath('message.body', 'جديد');

        $this->getJson(route('chat.general'))
            ->assertOk()
            ->assertJsonFragment(['body' => 'جديد']);
    }

    public function test_xss_body_is_stored_as_text_and_returned_in_json(): void
    {
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $payload = '<script>alert(1)</script>';
        $this->postJson(route('chat.general.send'), ['body' => $payload])->assertCreated();

        $this->getJson(route('chat.general'))
            ->assertOk()
            ->assertJsonFragment(['body' => $payload]);
    }
}
