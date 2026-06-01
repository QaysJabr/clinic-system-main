<?php

namespace Tests\Feature\Lab;

use App\Models\Invoice;
use App\Models\Patient;

/**
 * Double payment on same invoice must be rejected.
 */
final class ConcurrencyLabTest extends LabTestCase
{
    public function test_second_payment_over_remaining_is_rejected(): void
    {
        $patient = Patient::query()->create([
            'clinic_id' => $this->clinicAdmin->clinic_id,
            'file_number' => 'LAB-PAY-001',
            'full_name' => 'مريض دفع مزدوج',
            'status' => 'active',
        ]);

        $invoice = Invoice::query()->create([
            'clinic_id' => $this->clinicAdmin->clinic_id,
            'patient_id' => $patient->id,
            'invoice_number' => 'LAB-INV-001',
            'total' => 100,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAsClinicAdmin()
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->id,
                'amount' => 60,
                'payment_method' => 'cash',
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->actingAsClinicAdmin()
            ->from(route('invoices.show', $invoice))
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->id,
                'amount' => 50,
                'payment_method' => 'cash',
                'payment_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('amount');

        $invoice->refresh();
        $this->assertSame('partial', $invoice->status);
        $this->assertSame(1, $invoice->payments()->count());
    }
}
