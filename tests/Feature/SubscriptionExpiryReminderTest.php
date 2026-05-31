<?php

namespace Tests\Feature;

use App\Mail\SubscriptionExpiringMail;
use App\Models\AppNotification;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionReminderDispatch;
use App\Models\User;
use App\Support\AppNotificationType;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionExpiryReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);
    }

    public function test_command_sends_reminders_when_subscription_expires_in_seven_days(): void
    {
        Mail::fake();

        $owner = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);
        $clinic->forceFill([
            'owner_id' => $owner->id,
            'subscription_expires_at' => now()->addDays(7)->startOfDay()->addHours(12),
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'is_active' => true,
        ])->save();

        $plan = Plan::query()->where('slug', 'basic')->firstOrFail();
        $clinic->clinicSubscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => Plan::CYCLE_MONTHLY,
            'amount' => 29,
            'starts_at' => now()->subMonth(),
            'ends_at' => $clinic->subscription_expires_at,
        ]);

        Artisan::call('saas:send-subscription-reminders');

        Mail::assertSent(SubscriptionExpiringMail::class, function (SubscriptionExpiringMail $mail) use ($owner, $clinic) {
            return $mail->hasTo($owner->email)
                && $mail->clinic->is($clinic)
                && $mail->daysRemaining === 7;
        });

        $this->assertDatabaseHas('subscription_reminder_dispatches', [
            'clinic_id' => $clinic->id,
            'days_before' => 7,
            'channel' => SubscriptionReminderDispatch::CHANNEL_EMAIL,
        ]);

        $this->assertDatabaseHas('subscription_reminder_dispatches', [
            'clinic_id' => $clinic->id,
            'days_before' => 7,
            'channel' => SubscriptionReminderDispatch::CHANNEL_IN_APP,
        ]);

        $this->assertTrue(
            AppNotification::withoutGlobalScopes()
                ->where('clinic_id', $clinic->id)
                ->where('type', AppNotificationType::SUBSCRIPTION_EXPIRING)
                ->exists()
        );
    }

    public function test_reminder_is_not_sent_twice_for_same_window(): void
    {
        Mail::fake();

        $owner = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);
        $clinic->forceFill([
            'owner_id' => $owner->id,
            'subscription_expires_at' => now()->addDays(7),
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'is_active' => true,
        ])->save();

        foreach ([SubscriptionReminderDispatch::CHANNEL_EMAIL, SubscriptionReminderDispatch::CHANNEL_IN_APP] as $channel) {
            SubscriptionReminderDispatch::query()->create([
                'clinic_id' => $clinic->id,
                'days_before' => 7,
                'channel' => $channel,
                'sent_at' => now()->subDay(),
            ]);
        }

        Artisan::call('saas:send-subscription-reminders');

        Mail::assertNothingSent();
        $this->assertSame(
            0,
            AppNotification::withoutGlobalScopes()
                ->where('clinic_id', $clinic->id)
                ->where('type', AppNotificationType::SUBSCRIPTION_EXPIRING)
                ->count()
        );
    }
}
