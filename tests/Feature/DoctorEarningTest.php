<?php

namespace Tests\Feature;

use App\Enums\PaymentCycle;
use App\Enums\StaffCompensationModel;
use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\StaffCompensationProfile;
use App\Models\Visit;
use App\Services\DoctorEarningSyncService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorEarningTest extends TestCase
{
    use RefreshDatabase;

    private function makeDoctorWithPercentageProfile(): Doctor
    {
        $staff = Staff::query()->create([
            'full_name' => 'طبيب أرباح',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);

        StaffCompensationProfile::query()->create([
            'staff_id' => $staff->id,
            'compensation_type' => StaffCompensationModel::Percentage,
            'payment_cycle' => PaymentCycle::Monthly,
            'percentage_rate' => 10,
            'status' => 'active',
            'start_date' => now()->toDateString(),
        ]);

        return Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب أرباح',
            'status' => 'active',
        ]);
    }

    public function test_earning_synced_from_invoice_total_and_persists_when_invoice_marked_unpaid(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctor = $this->makeDoctorWithPercentageProfile();

        $patient = Patient::query()->create([
            'file_number' => 'E-001',
            'full_name' => 'مريض',
            'status' => 'active',
        ]);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_COMPLETED,
        ]);

        $invoice = Invoice::query()->create([
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'invoice_number' => 'INV-TEST-001',
            'total' => 100,
            'paid' => 100,
            'status' => 'paid',
        ]);

        app(DoctorEarningSyncService::class)->syncFromPaidInvoice($invoice->fresh());

        $this->assertDatabaseHas('doctor_earnings', [
            'invoice_id' => $invoice->id,
            'doctor_id' => $doctor->id,
            'status' => DoctorEarning::STATUS_PENDING,
            'earning_amount' => 10,
        ]);

        $invoice->update([
            'status' => 'unpaid',
            'paid' => 0,
        ]);

        app(DoctorEarningSyncService::class)->syncFromPaidInvoice($invoice->fresh());

        $this->assertDatabaseHas('doctor_earnings', [
            'invoice_id' => $invoice->id,
            'doctor_id' => $doctor->id,
            'earning_amount' => 10,
        ]);
    }

    public function test_earning_removed_when_invoice_total_zeroed(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctor = $this->makeDoctorWithPercentageProfile();

        $patient = Patient::query()->create([
            'file_number' => 'E-002',
            'full_name' => 'مريض 2',
            'status' => 'active',
        ]);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_COMPLETED,
        ]);

        $invoice = Invoice::query()->create([
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'invoice_number' => 'INV-TEST-002',
            'total' => 100,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        app(DoctorEarningSyncService::class)->syncFromInvoice($invoice->fresh());

        $this->assertDatabaseHas('doctor_earnings', ['invoice_id' => $invoice->id]);

        $invoice->update(['total' => 0]);

        app(DoctorEarningSyncService::class)->syncFromInvoice($invoice->fresh());

        $this->assertDatabaseMissing('doctor_earnings', [
            'invoice_id' => $invoice->id,
        ]);
    }
}
