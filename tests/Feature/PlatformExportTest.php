<?php

namespace Tests\Feature;

use App\Mail\ManualSubscriptionPaymentMail;
use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformExportTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed([RolePermissionSeeder::class, PlanSeeder::class]);

        return User::query()
            ->where('email', config('platform.owner_email'))
            ->firstOrFail();
    }

    public function test_super_admin_can_export_clinics_csv(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->get(route('platform.clinics.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('.csv', (string) $response->headers->get('content-disposition'));
    }

    public function test_super_admin_can_export_payments_csv_with_date_filter(): void
    {
        $super = $this->superAdmin();
        $clinic = Clinic::query()->firstOrFail();

        SubscriptionPayment::query()->create([
            'clinic_id' => $clinic->id,
            'amount' => 50,
            'paid_at' => now()->subDays(10),
            'status' => SubscriptionPayment::STATUS_SUCCEEDED,
            'source' => SubscriptionPayment::SOURCE_MANUAL,
        ]);

        $this->actingAs($super);

        $response = $this->get(route('platform.clinics.payments.export', [
            'clinic' => $clinic,
            'paid_from' => now()->subDays(30)->toDateString(),
            'paid_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_manual_payment_sends_email_to_clinic_owner(): void
    {
        Mail::fake();

        $super = $this->superAdmin();
        $clinic = Clinic::query()->firstOrFail();

        $owner = User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'clinic-owner-'.uniqid().'@test.local',
        ]);
        $clinic->forceFill(['owner_id' => $owner->id])->save();

        $this->actingAs($super);

        $this->post(route('platform.clinics.record-payment', $clinic), [
            'amount' => 99.5,
            'notes' => 'export test',
        ])->assertRedirect(route('platform.clinics.subscription', $clinic));

        Mail::assertSent(ManualSubscriptionPaymentMail::class, function (ManualSubscriptionPaymentMail $mail) use ($owner): bool {
            return $mail->hasTo($owner->email);
        });
    }
}
