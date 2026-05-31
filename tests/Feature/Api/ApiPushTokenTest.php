<?php

namespace Tests\Feature\Api;

use App\Jobs\SendPushToUserJob;
use App\Models\AppNotification;
use App\Models\DevicePushToken;
use App\Models\User;
use App\Support\AppNotificationType;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiPushTokenTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->token = (string) $this->postJson(route('api.v1.auth.login'), [
            'email' => 'admin@clinic.local',
            'password' => 'password',
        ])->json('data.token');
    }

    public function test_register_push_token(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson(route('api.v1.push.register'), [
                'token' => 'fcm-test-token-abc',
                'platform' => 'android',
                'device_name' => 'Pixel Test',
            ])
            ->assertOk()
            ->assertJsonPath('data.platform', 'android');

        $this->assertDatabaseHas('device_push_tokens', [
            'user_id' => $this->admin->id,
            'token' => 'fcm-test-token-abc',
            'platform' => 'android',
        ]);
    }

    public function test_unregister_push_token(): void
    {
        DevicePushToken::query()->create([
            'user_id' => $this->admin->id,
            'clinic_id' => $this->admin->clinic_id,
            'token' => 'fcm-remove-me',
            'platform' => 'ios',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson(route('api.v1.push.unregister'), [
                'token' => 'fcm-remove-me',
            ])
            ->assertOk()
            ->assertJsonPath('data.deleted', 1);

        $this->assertDatabaseMissing('device_push_tokens', [
            'token' => 'fcm-remove-me',
        ]);
    }

    public function test_in_app_notification_dispatches_push_job_when_enabled(): void
    {
        config(['push.enabled' => true]);

        DevicePushToken::query()->create([
            'user_id' => $this->admin->id,
            'clinic_id' => $this->admin->clinic_id,
            'token' => 'fcm-dispatch',
            'platform' => 'android',
        ]);

        Queue::fake();

        AppNotification::query()->create([
            'user_id' => $this->admin->id,
            'clinic_id' => $this->admin->clinic_id,
            'type' => AppNotificationType::APPOINTMENT_TODAY,
            'title' => 'موعد جديد',
            'message' => 'تفاصيل الموعد',
            'is_read' => false,
        ]);

        Queue::assertPushed(SendPushToUserJob::class, function (SendPushToUserJob $job): bool {
            return $job->userId === $this->admin->id
                && $job->title === 'موعد جديد';
        });
    }

    public function test_meta_includes_push_config(): void
    {
        $this->getJson(route('api.v1.meta'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'push' => ['enabled', 'register_path', 'platforms'],
                ],
            ]);
    }
}
