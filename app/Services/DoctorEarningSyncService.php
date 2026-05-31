<?php

namespace App\Services;

use App\Enums\StaffCompensationModel;
use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\Invoice;
use App\Models\Staff;
use App\Models\StaffCompensationProfile;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * مزامنة سجلات أرباح الأطباء (نسبة) من الفواتير المربوطة بزيارة — محاسبة الاستحقاق (accrual).
 *
 * مبلغ النسبة = إجمالي الفاتورة × (النسبة/100) حتى لو لم تُسدَّد الفاتورة بعد؛
 * حقل status في doctor_earnings يعبّر عن تسوية العيادة مع الطبيب وليس عن دفع المريض.
 *
 * doctor_earnings.doctor_id = doctors.id (المعالج على الزيارة).
 */
final class DoctorEarningSyncService
{
    /**
     * يعيد محاذاة سجل الأرباح للفاتورة: ينشئ/يحدّث عند توفر زيارة وملف تعويض نسبة،
     * أو يحذف السجل عند عدم الاستحقاق.
     */
    public function syncFromInvoice(Invoice $invoice): void
    {
        if (! $invoice->visit_id) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $clinicId = $invoice->clinic_id;
        if ($clinicId === null) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $visit = Visit::withoutGlobalScopes()
            ->whereKey($invoice->visit_id)
            ->where('clinic_id', $clinicId)
            ->first();
        if (! $visit) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        if ((int) $invoice->patient_id !== (int) $visit->patient_id) {
            Log::warning('doctor_earning_invoice_visit_patient_mismatch', [
                'invoice_id' => $invoice->id,
                'invoice_patient_id' => $invoice->patient_id,
                'visit_patient_id' => $visit->patient_id,
            ]);
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $doctor = Doctor::withoutGlobalScopes()
            ->whereKey($visit->doctor_id)
            ->where('clinic_id', $clinicId)
            ->first();
        if (! $doctor || ! $doctor->staff_id) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $staff = Staff::withoutGlobalScopes()
            ->whereKey($doctor->staff_id)
            ->where('clinic_id', $clinicId)
            ->first();

        if (! $staff || $staff->role_type !== 'doctor' || $staff->status !== 'active') {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $profile = StaffCompensationProfile::withoutGlobalScopes()
            ->where('staff_id', $staff->id)
            ->where('clinic_id', $clinicId)
            ->first();
        if (! $profile || $profile->status !== 'active') {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        if ($profile->compensation_type !== StaffCompensationModel::Percentage) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $rate = (float) ($profile->percentage_rate ?? 0);
        if ($rate <= 0) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $invoiceTotal = round((float) $invoice->total, 2);
        if ($invoiceTotal <= 0) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        $earningAmount = round($invoiceTotal * $rate / 100, 2);
        if ($earningAmount < 0) {
            $this->removeEarningForInvoice($invoice->id);

            return;
        }

        try {
            DB::transaction(function () use ($invoice, $doctor, $invoiceTotal, $rate, $earningAmount, $clinicId): void {
                Invoice::withoutGlobalScopes()
                    ->whereKey($invoice->id)
                    ->where('clinic_id', $clinicId)
                    ->lockForUpdate()
                    ->first();

                $existing = DoctorEarning::withoutGlobalScopes()
                    ->where('invoice_id', $invoice->id)
                    ->where('clinic_id', $clinicId)
                    ->first();

                $payload = [
                    'clinic_id' => $invoice->clinic_id,
                    'doctor_id' => $doctor->id,
                    'visit_id' => $invoice->visit_id,
                    'total_amount' => $invoiceTotal,
                    'percentage_rate' => $rate,
                    'earning_amount' => $earningAmount,
                ];

                if ($existing && $existing->status === DoctorEarning::STATUS_PAID) {
                    $payload['status'] = DoctorEarning::STATUS_PAID;
                    $payload['paid_at'] = $existing->paid_at;
                } else {
                    $payload['status'] = DoctorEarning::STATUS_PENDING;
                }

                DoctorEarning::withoutGlobalScopes()->updateOrCreate(
                    ['invoice_id' => $invoice->id],
                    $payload
                );
            });
        } catch (\Throwable $e) {
            Log::warning('doctor_earning_sync_failed', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function removeEarningForInvoice(int $invoiceId): void
    {
        DoctorEarning::withoutGlobalScopes()->where('invoice_id', $invoiceId)->delete();
    }

    /**
     * عند اكتمال زيارة: إعادة مزامنة كل الفواتير المرتبطة بنفس الزيارة (أي حالة دفع).
     */
    public function syncInvoicesForVisit(int $visitId): void
    {
        $visitClinicId = Visit::withoutGlobalScopes()->whereKey($visitId)->value('clinic_id');
        if ($visitClinicId === null) {
            return;
        }

        Invoice::withoutGlobalScopes()
            ->where('visit_id', $visitId)
            ->where('clinic_id', $visitClinicId)
            ->each(fn (Invoice $inv) => $this->syncFromInvoice($inv));
    }

    /** @deprecated Use {@see syncFromInvoice} */
    public function syncFromPaidInvoice(Invoice $invoice): void
    {
        $this->syncFromInvoice($invoice);
    }

    /** @deprecated Use {@see syncInvoicesForVisit} */
    public function syncPaidInvoicesForVisit(int $visitId): void
    {
        $this->syncInvoicesForVisit($visitId);
    }
}
