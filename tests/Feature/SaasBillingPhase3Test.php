<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Support\SaasBillingAccess;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookHandled;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SaasBillingPhase3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);
    }

    public function test_saas_billing_access_for_owner_and_billing_permission(): void
    {
        $owner = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);
        $clinic->forceFill(['owner_id' => $owner->id])->save();

        $this->assertTrue(SaasBillingAccess::canManage($owner, $clinic));

        $manager = User::query()->create([
            'name' => 'Manager',
            'email' => 'mgr-'.uniqid().'@clinic.local',
            'password' => bcrypt('password'),
            'clinic_id' => $clinic->id,
            'email_verified_at' => now(),
        ]);
        $manager->givePermissionTo(Permission::findOrCreate('manage billing', 'web'));

        $this->assertFalse(SaasBillingAccess::canManage($manager, $clinic));

        $manager->assignRole('admin');
        $this->assertTrue(SaasBillingAccess::canManage($manager, $clinic));
    }

    public function test_webhook_handled_event_is_logged(): void
    {
        $clinic = Clinic::query()->firstOrFail();
        $clinic->forceFill(['stripe_id' => 'cus_test_123'])->save();

        WebhookHandled::dispatch([
            'id' => 'evt_test_'.uniqid(),
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_test',
                    'customer' => 'cus_test_123',
                    'status' => 'active',
                ],
            ],
        ]);

        $this->assertDatabaseHas('stripe_webhook_events', [
            'clinic_id' => $clinic->id,
            'event_type' => 'customer.subscription.updated',
            'stripe_customer_id' => 'cus_test_123',
        ]);

        $this->assertInstanceOf(StripeWebhookEvent::class, StripeWebhookEvent::query()->first());
    }
}
