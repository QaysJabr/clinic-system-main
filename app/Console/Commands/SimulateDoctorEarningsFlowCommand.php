<?php

namespace App\Console\Commands;

use App\Enums\PaymentCycle;
use App\Enums\StaffCompensationModel;
use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\StaffCompensationProfile;
use App\Models\Visit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * End-to-end simulation: staff (doctor) + % profile + visit + invoice + full payment → doctor_earnings.
 * Does not change application code paths; uses the same update pattern as PaymentController for the payment step.
 */
final class SimulateDoctorEarningsFlowCommand extends Command
{
    protected $signature = 'clinic:simulate-doctor-earnings-flow {--cleanup : Delete created simulation rows afterward}';

    protected $description = 'Simulate doctor percentage earnings flow and report whether doctor_earnings was created';

    public function handle(): int
    {
        $suffix = Str::upper(Str::random(6));

        $this->info('=== Doctor earnings flow simulation ===');
        $this->newLine();

        /** @var list<int> $cleanupIds ['patient'=>, 'staff'=>, 'doctor'=>, 'visit'=>, 'invoice'=>] */
        $cleanupIds = [
            'patient' => null,
            'staff' => null,
            'doctor' => null,
            'visit' => null,
            'invoice' => null,
        ];

        try {
            DB::transaction(function () use ($suffix, &$cleanupIds): void {
                $this->line('Step 1: Patient');
                $patient = Patient::query()->create([
                    'file_number' => 'SIM-'.$suffix,
                    'full_name' => 'مريض محاكاة '.$suffix,
                    'status' => 'active',
                ]);
                $cleanupIds['patient'] = $patient->id;
                $this->info("  OK patient_id={$patient->id}");

                $this->line('Step 2: Staff (role doctor)');
                $staff = Staff::query()->create([
                    'full_name' => 'طبيب محاكاة '.$suffix,
                    'role_type' => 'doctor',
                    'phone' => null,
                    'email' => 'sim.dr.'.$suffix.'@test.local',
                    'status' => 'active',
                ]);
                $cleanupIds['staff'] = $staff->id;
                $this->info("  OK staff_id={$staff->id} role={$staff->role_type}");

                $this->line('Step 3: Compensation profile (percentage 30%)');
                $profile = StaffCompensationProfile::query()->create([
                    'staff_id' => $staff->id,
                    'compensation_type' => StaffCompensationModel::Percentage,
                    'payment_cycle' => PaymentCycle::Monthly,
                    'base_salary' => null,
                    'percentage_rate' => '30.00',
                    'daily_wage' => null,
                    'calculation_basis' => 'invoice_paid_total',
                    'start_date' => null,
                    'status' => 'active',
                    'notes' => null,
                ]);
                $this->info("  OK profile_id={$profile->id} rate={$profile->percentage_rate}%");

                $this->line('Step 4: Doctor linked to staff');
                $doctor = Doctor::query()->create([
                    'staff_id' => $staff->id,
                    'full_name' => 'طبيب محاكاة '.$suffix,
                    'specialty' => null,
                    'phone' => null,
                    'email' => 'doctor.sim.'.$suffix.'@test.local',
                    'license_number' => 'LIC-SIM-'.$suffix,
                    'room_number' => null,
                    'status' => 'active',
                    'notes' => null,
                ]);
                $cleanupIds['doctor'] = $doctor->id;
                $doctor->refresh();
                $this->info("  OK doctor_id={$doctor->id} staff_id=".($doctor->staff_id ?? 'NULL'));

                $this->line('Step 5: Visit');
                $visit = Visit::query()->create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_id' => null,
                    'visit_date' => now()->toDateString(),
                    'chief_complaint' => null,
                    'diagnosis' => null,
                    'treatment_plan' => null,
                    'notes' => 'محاكاة أرباح',
                    'status' => 'completed',
                ]);
                $cleanupIds['visit'] = $visit->id;
                $this->info("  OK visit_id={$visit->id} patient_id={$visit->patient_id} doctor_id={$visit->doctor_id}");

                $this->line('Step 6: Invoice + line items (total 100.00)');
                $invoiceNumber = 'INV-SIM-'.$suffix.'-'.now()->format('Ymd');

                $invoice = Invoice::query()->create([
                    'patient_id' => $patient->id,
                    'visit_id' => $visit->id,
                    'invoice_number' => $invoiceNumber,
                    'notes' => null,
                    'total' => '100.00',
                    'paid' => '0.00',
                    'status' => 'unpaid',
                ]);

                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'item_name' => 'خدمة محاكاة',
                    'price' => '100.00',
                    'quantity' => 1,
                    'total' => '100.00',
                ]);

                $invoice->refresh();
                $cleanupIds['invoice'] = $invoice->id;
                $this->info("  OK invoice_id={$invoice->id} visit_id=".($invoice->visit_id ?? 'NULL').' doctor_id='.($invoice->doctor_id ?? 'NULL'));
                $this->info('  Preconditions: invoice.patient_id matches visit.patient_id → '.(((int) $invoice->patient_id === (int) $visit->patient_id) ? 'YES' : 'NO'));

                $this->line('Step 7: Payment (full 100.00, cash) — same logic as PaymentController');

                Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'amount' => '100.00',
                    'payment_method' => 'cash',
                    'payment_date' => now()->toDateString(),
                    'notes' => 'محاكاة',
                ]);

                $invoice->refresh();
                $paid = round((float) $invoice->payments()->sum('amount'), 2);
                $total = round((float) $invoice->total, 2);
                $status = $this->paymentStatus($total, $paid);

                $invoice->update([
                    'paid' => number_format($paid, 2, '.', ''),
                    'status' => $status,
                ]);

                $invoice->refresh();

                $this->info("  OK paid={$invoice->paid} status={$invoice->status} (expected status=paid)");
            });

            $invoiceId = $cleanupIds['invoice'];
            $invoice = Invoice::query()->with(['visit.doctor.staff.compensationProfile'])->find($invoiceId);

            $this->newLine();
            $this->line('--- Trace: DoctorEarningSyncService ---');
            $this->line('  Trigger: InvoiceObserver::saved → DoctorEarningSyncService::syncFromInvoice');
            $this->line('  Conditions for creation: visit_id set, visit.doctor.staff_id, staff role doctor active, profile active percentage, rate>0, invoice total>0 (accrual — unpaid allowed)');

            $earning = DoctorEarning::query()->where('invoice_id', $invoiceId)->first();

            $this->newLine();
            if ($earning) {
                $this->info('RESULT: doctor_earnings row CREATED.');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['id', $earning->id],
                        ['doctor_id (doctors)', $earning->doctor_id],
                        ['invoice_id', $earning->invoice_id],
                        ['visit_id', $earning->visit_id],
                        ['total_amount', $earning->total_amount],
                        ['percentage_rate', $earning->percentage_rate],
                        ['earning_amount', $earning->earning_amount],
                        ['status', $earning->status],
                    ]
                );
                $expected = round(100 * 30 / 100, 2);
                if ((float) $earning->earning_amount === $expected) {
                    $this->info("  Earning math OK (30% of 100 = {$expected}).");
                } else {
                    $this->warn('  Earning amount differs from expected 30.00 — check rounding.');
                }

                $visitDoctorId = $invoice?->visit?->doctor_id;
                if ($visitDoctorId !== null && (int) $earning->doctor_id === (int) $visitDoctorId) {
                    $this->info('  doctor_earnings.doctor_id matches visit.doctor_id (doctors.id).');
                } elseif ($visitDoctorId !== null) {
                    $this->error('  doctor_earnings.doctor_id does NOT match visit.doctor_id — FK regression.');
                }
            } else {
                $this->error('RESULT: NO doctor_earnings row — flow broken or preconditions failed.');
                $this->warn('Diagnostics (reload invoice):');
                if ($invoice) {
                    $this->line('  invoice.status='.$invoice->status.' total='.$invoice->total.' visit_id='.($invoice->visit_id ?? 'null'));
                    $v = $invoice->visit;
                    $d = $v?->doctor;
                    $st = $d?->staff;
                    $p = $st?->compensationProfile;
                    $this->line('  visit='.($v ? 'yes' : 'no').' doctor='.($d ? 'yes' : 'no').' staff_id='.($d->staff_id ?? 'null'));
                    $this->line('  staff='.($st ? 'yes' : 'no').' role='.($st->role_type ?? '—').' status='.($st->status ?? '—'));
                    $this->line('  profile='.($p ? 'yes' : 'no').' type='.($p->compensation_type->value ?? '—').' active='.($p->status ?? '—'));
                }
            }

            if ($this->option('cleanup') && $invoiceId) {
                $this->cleanupSimulation($cleanupIds);
                $this->info('Cleanup done (--cleanup).');
            } else {
                $this->newLine();
                $this->comment('Simulation rows left in DB for inspection. Run with --cleanup to delete them.');
            }

            return $earning ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            report($e);

            return self::FAILURE;
        }
    }

    private function paymentStatus(float $total, float $paid): string
    {
        $t = round($total, 2);
        $p = round($paid, 2);
        if ($p <= 0) {
            return 'unpaid';
        }
        if ($p < $t) {
            return 'partial';
        }

        return 'paid';
    }

    /**
     * @param  array<string, int|null>  $ids
     */
    private function cleanupSimulation(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            if (! empty($ids['invoice'])) {
                Payment::query()->where('invoice_id', $ids['invoice'])->delete();
                InvoiceItem::query()->where('invoice_id', $ids['invoice'])->delete();
                DoctorEarning::query()->where('invoice_id', $ids['invoice'])->delete();
                Invoice::query()->whereKey($ids['invoice'])->delete();
            }
            if (! empty($ids['visit'])) {
                Visit::query()->whereKey($ids['visit'])->delete();
            }
            if (! empty($ids['doctor'])) {
                Doctor::query()->whereKey($ids['doctor'])->forceDelete();
            }
            if (! empty($ids['staff'])) {
                StaffCompensationProfile::query()->where('staff_id', $ids['staff'])->delete();
                Staff::query()->whereKey($ids['staff'])->delete();
            }
            if (! empty($ids['patient'])) {
                Patient::query()->whereKey($ids['patient'])->forceDelete();
            }
        });
    }
}
