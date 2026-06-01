<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ApiTestHelpers;
use Tests\TestCase;

final class ApiInvoicesPaymentsTest extends TestCase
{
    use ApiTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedApiDefaults();
    }

    public function test_invoices_index_returns_list(): void
    {
        $patient = Patient::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'file_number' => 'API-INV-P',
            'full_name' => 'مريض فاتورة',
            'status' => 'active',
        ]);

        Invoice::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'patient_id' => $patient->id,
            'invoice_number' => 'API-INV-001',
            'total' => 200,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        $this->apiGet(route('api.v1.invoices.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_record_payment_via_api(): void
    {
        $patient = Patient::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'file_number' => 'API-PAY-P',
            'full_name' => 'مريض دفع',
            'status' => 'active',
        ]);

        $invoice = Invoice::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'patient_id' => $patient->id,
            'invoice_number' => 'API-PAY-INV',
            'total' => 100,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        $this->apiPost(route('api.v1.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 50,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.amount', '50.00');

        $invoice->refresh();
        $this->assertSame('partial', $invoice->status);
    }

    public function test_payment_rejects_amount_over_remaining(): void
    {
        $patient = Patient::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'file_number' => 'API-PAY-P2',
            'full_name' => 'مريض دفع 2',
            'status' => 'active',
        ]);

        $invoice = Invoice::query()->create([
            'clinic_id' => $this->apiUser->clinic_id,
            'patient_id' => $patient->id,
            'invoice_number' => 'API-PAY-INV2',
            'total' => 100,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        $this->apiPost(route('api.v1.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 150,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ])->assertUnprocessable();
    }
}
