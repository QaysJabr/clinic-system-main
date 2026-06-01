<?php

namespace Tests\Feature\Lab;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;

/**
 * End-to-end: patient → appointment → check-in → invoice → payment.
 */
final class ClinicDayFlowLabTest extends LabTestCase
{
    public function test_full_clinic_day_flow_via_http(): void
    {
        $doctor = Doctor::query()->create([
            'full_name' => 'طبيب Lab',
            'status' => 'active',
            'clinic_id' => $this->clinicAdmin->clinic_id,
        ]);

        $this->actingAsClinicAdmin()
            ->post(route('patients.store'), [
                'full_name' => 'مريض Lab يوم كامل',
                'phone' => '0599000111',
                'gender' => 'male',
                'status' => 'active',
            ])
            ->assertRedirect(route('patients.index'));

        $patient = Patient::query()->where('full_name', 'مريض Lab يوم كامل')->first();
        $this->assertNotNull($patient);

        $date = now()->addDay()->toDateString();

        $this->actingAsClinicAdmin()
            ->post(route('appointments.store'), [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
                'status' => AppointmentStatus::Scheduled->value,
                'reason' => 'فحص Lab',
            ])
            ->assertRedirect();

        $appointment = Appointment::query()
            ->where('patient_id', $patient->id)
            ->where('doctor_id', $doctor->id)
            ->first();

        $this->assertNotNull($appointment);

        $this->actingAsClinicAdmin()
            ->postJson(route('appointments.calendar.check-in', $appointment))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $visit = Visit::query()->where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($visit);

        $this->actingAsClinicAdmin()
            ->post(route('invoices.store'), [
                'patient_id' => $patient->id,
                'visit_id' => $visit->id,
                'items' => [
                    [
                        'item_name' => 'كشفية',
                        'price' => 100,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $invoice = Invoice::query()->where('visit_id', $visit->id)->first();
        $this->assertNotNull($invoice);
        $this->assertSame('unpaid', $invoice->status);

        $this->actingAsClinicAdmin()
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->id,
                'amount' => 100,
                'payment_method' => 'cash',
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(1, Payment::query()->where('invoice_id', $invoice->id)->count());
    }
}
