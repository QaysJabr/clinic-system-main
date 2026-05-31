<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);
    }

    private function clinicOwner(): User
    {
        $owner = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);
        $clinic->forceFill(['owner_id' => $owner->id])->save();

        return $owner;
    }

    public function test_billing_page_renders_for_clinic_owner(): void
    {
        $owner = $this->clinicOwner();
        $plan = Plan::query()->where('slug', 'basic')->firstOrFail();

        app(SubscriptionService::class)->subscribe($clinic = Clinic::query()->findOrFail((int) $owner->clinic_id), $plan, Plan::CYCLE_MONTHLY);

        SubscriptionPayment::query()->create([
            'clinic_id' => $clinic->id,
            'amount' => 29.00,
            'paid_at' => now(),
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
            'source' => SubscriptionPayment::SOURCE_MANUAL,
        ]);

        $this->actingAs($owner);

        $this->get(route('saas.billing'))
            ->assertOk()
            ->assertSee(__('saas.page_title'), false)
            ->assertSee(__('saas.section_subscription'), false)
            ->assertSee(__('saas.section_payments'), false)
            ->assertSee(__('saas.btn_cancel_subscription'), false)
            ->assertSee('29.00', false);
    }

    public function test_staff_without_billing_permission_sees_owner_hint_without_cancel_button(): void
    {
        $owner = $this->clinicOwner();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);

        $receptionist = User::query()->create([
            'name' => 'Reception',
            'email' => 'reception-'.uniqid().'@clinic.local',
            'password' => bcrypt('password'),
            'clinic_id' => $clinic->id,
            'email_verified_at' => now(),
        ]);
        $receptionist->assignRole('receptionist');

        $this->actingAs($receptionist);

        $response = $this->get(route('saas.billing'));
        $response->assertOk();
        $response->assertSee(__('saas.owner_only_hint', ['owner' => $owner->name]), false);
        $response->assertDontSee('id="saas-billing-cancel-btn"', false);
    }

    public function test_billing_manager_admin_can_cancel_subscription(): void
    {
        $owner = $this->clinicOwner();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);

        $billingAdmin = User::query()->create([
            'name' => 'Billing Admin',
            'email' => 'billing-admin-'.uniqid().'@clinic.local',
            'password' => bcrypt('password'),
            'clinic_id' => $clinic->id,
            'email_verified_at' => now(),
        ]);
        $billingAdmin->assignRole('admin');

        $plan = Plan::query()->where('slug', 'basic')->firstOrFail();
        app(SubscriptionService::class)->subscribe($clinic, $plan, Plan::CYCLE_MONTHLY);

        $this->actingAs($billingAdmin);

        $this->get(route('saas.billing'))
            ->assertOk()
            ->assertSee('id="saas-billing-cancel-btn"', false);

        $this->postJson(route('saas.billing.cancel'))->assertOk();
    }

    public function test_owner_can_cancel_subscription_via_json(): void
    {
        $owner = $this->clinicOwner();
        $plan = Plan::query()->where('slug', 'basic')->firstOrFail();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);

        app(SubscriptionService::class)->subscribe($clinic, $plan, Plan::CYCLE_MONTHLY);

        $this->actingAs($owner);

        $this->postJson(route('saas.billing.cancel'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['billing']]);

        $clinic->refresh();
        $this->assertNotEquals('active', $clinic->latestClinicSubscription?->status);
    }

    public function test_receptionist_cannot_cancel_subscription(): void
    {
        $owner = $this->clinicOwner();
        $clinic = Clinic::query()->findOrFail((int) $owner->clinic_id);

        $receptionist = User::query()->create([
            'name' => 'Other',
            'email' => 'cancel-deny-'.uniqid().'@clinic.local',
            'password' => bcrypt('password'),
            'clinic_id' => $clinic->id,
            'email_verified_at' => now(),
        ]);
        $receptionist->assignRole('receptionist');

        $this->actingAs($receptionist);

        $this->postJson(route('saas.billing.cancel'))->assertForbidden();
    }

    public function test_owner_can_download_billing_pdf(): void
    {
        $owner = $this->clinicOwner();

        $this->actingAs($owner);

        $response = $this->get(route('saas.billing.pdf'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
