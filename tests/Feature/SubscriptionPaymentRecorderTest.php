<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionPaymentRecorder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class SubscriptionPaymentRecorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_payment_is_recorded(): void
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);
        $clinic = Clinic::query()->firstOrFail();

        $payment = app(SubscriptionPaymentRecorder::class)->recordManual($clinic, 50.25, now(), 'test');

        $this->assertDatabaseHas('subscription_payments', [
            'id' => $payment->id,
            'clinic_id' => $clinic->id,
            'amount' => '50.25',
            'source' => SubscriptionPayment::SOURCE_MANUAL,
        ]);
    }

    public function test_stripe_payment_reference_is_idempotent(): void
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);
        $clinic = Clinic::query()->firstOrFail();
        $plan = Plan::query()->firstOrFail();
        $clinic->forceFill(['plan_id' => $plan->id])->save();

        $subscription = new Subscription([
            'type' => 'default',
            'stripe_id' => 'sub_test_123',
            'stripe_status' => 'active',
            'stripe_price' => $plan->stripe_price_id,
            'quantity' => 1,
        ]);
        $subscription->clinic_id = $clinic->id;
        $subscription->current_period_end = now()->addMonth();

        $recorder = app(SubscriptionPaymentRecorder::class);
        $first = $recorder->recordStripeSubscriptionPeriod($clinic, $subscription);
        $second = $recorder->recordStripeSubscriptionPeriod($clinic, $subscription);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, SubscriptionPayment::query()->where('source', SubscriptionPayment::SOURCE_STRIPE)->count());
    }
}
