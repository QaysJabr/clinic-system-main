<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\StaffPayment;
use App\Models\User;
use App\Models\Visit;
use App\Services\Push\PushNotificationDispatcher;
use App\Support\AppNotificationType;
use App\Support\ClinicPermissions;
use App\Support\ClinicSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

class InAppNotificationService
{
    /**
     * مستخدمو المواعيد: مدير، استقبال، طبيب (حسب الأدوار الافتراضية) + أي من لديه صلاحية جدولة المواعيد (بما فيها الموروثة من الدور عبر Spatie).
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForAppointments(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'receptionist', 'doctor'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_APPOINTMENTS));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * فواتير ودفعات: مدير، محاسب، استقبال (لديهم فواتير في السيدر) + صلاحية الفواتير/الدفعات.
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForFinance(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'accountant', 'receptionist'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_INVOICES))
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_PAYMENTS));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * رواتب الموظفين: مدير + محاسب + من لديه إدارة الرواتب (صلاحية موروثة أو مباشرة).
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForPayroll(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'accountant'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_PAYROLL));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * أرباح الأطباء (إدارة): مدير، محاسب، من يدير أرباح الأطباء + حساب الطبيب المرتبط بسجل الموظف إن وُجد.
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForDoctorEarningManagement(int $clinicId, ?int $doctorId = null): Collection
    {
        $ids = $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'accountant'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_DOCTOR_EARNINGS));
            })
            ->pluck('id');

        if ($doctorId !== null && $doctorId > 0) {
            $ids = $ids->merge($this->recipientUserIdsLinkedToDoctor($clinicId, $doctorId));
        }

        return $ids->unique()->values();
    }

    /**
     * معرّف المستخدم المرتبط بحساب الطبيب (موظف → user_id).
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsLinkedToDoctor(int $clinicId, int $doctorId): Collection
    {
        $staffId = Doctor::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereKey($doctorId)
            ->value('staff_id');

        if (! $staffId) {
            return collect();
        }

        $userId = Staff::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereKey($staffId)
            ->value('user_id');

        if (! $userId) {
            return collect();
        }

        return collect([(int) $userId]);
    }

    /**
     * زيارات: مدير، استقبال، طبيب + صلاحية الزيارات.
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForVisits(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'receptionist', 'doctor'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_VISITS));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * مرضى: مدير، استقبال، طبيب + صلاحية المرضى.
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForPatients(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'receptionist', 'doctor'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_PATIENTS));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * مصروفات: مدير، محاسب + صلاحية المصروفات.
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForExpenses(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'accountant'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_EXPENSES));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * مديرو المنصّة (بدون عيادة مرتبطة).
     *
     * @return Collection<int, int>
     */
    public function recipientUserIdsForPlatformSuperAdmins(): Collection
    {
        return User::query()
            ->withoutGlobalScopes()
            ->whereNull('clinic_id')
            ->whereHas('roles', fn ($r) => $r->where('name', 'super_admin'))
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * @param  Builder<User>  $query
     */
    private function usersInClinic(int $clinicId): Builder
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('users.clinic_id', $clinicId);
    }

    public function notifyDoctorEarningCreated(DoctorEarning $earning): void
    {
        $clinicId = (int) $earning->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        $recipients = $this->recipientUserIdsForDoctorEarningManagement($clinicId, (int) $earning->doctor_id);
        if ($recipients->isEmpty()) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::DOCTOR_EARNING_CREATED,
            DoctorEarning::class,
            $earning->id,
            now()->subHours(4),
            now(),
            $clinicId
        )) {
            return;
        }

        $earning->loadMissing(['doctor.staff', 'invoice']);
        $cur = ClinicSettings::current()->currency ?? '';
        $suffix = filled($cur) ? ' '.$cur : '';
        $doctorName = optional(optional($earning->doctor)->staff)->full_name
            ?? optional($earning->doctor)->full_name
            ?? '—';
        $amount = number_format((float) $earning->earning_amount, 2);
        $inv = $earning->invoice?->invoice_number ?? ('#'.$earning->invoice_id);

        $title = 'أرباح طبيب جديدة';
        $message = "سجل أرباح جديد للطبيب {$doctorName} بقيمة {$amount}{$suffix} — فاتورة {$inv}";

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::DOCTOR_EARNING_CREATED,
            'title' => $title,
            'message' => $message,
            'related_type' => DoctorEarning::class,
            'related_id' => $earning->id,
        ]);

        $this->notifyDoctorEarningPendingSummaryForDoctor($clinicId, (int) $earning->doctor_id);
    }

    public function notifyDoctorEarningPendingSummaryForDoctor(int $clinicId, int $doctorId): void
    {
        $recipients = $this->recipientUserIdsForDoctorEarningManagement($clinicId, $doctorId);
        if ($recipients->isEmpty()) {
            return;
        }

        $pendingTotal = (float) DoctorEarning::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->where('status', DoctorEarning::STATUS_PENDING)
            ->sum('earning_amount');

        if ($pendingTotal <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::DOCTOR_EARNING_PENDING,
            Doctor::class,
            $doctorId,
            today()->startOfDay(),
            today()->endOfDay(),
            $clinicId
        )) {
            return;
        }

        $doctor = Doctor::withoutGlobalScopes()->with('staff')->where('clinic_id', $clinicId)->whereKey($doctorId)->first();
        $doctorName = optional(optional($doctor)->staff)->full_name
            ?? optional($doctor)->full_name
            ?? '—';
        $cur = ClinicSettings::current()->currency ?? '';
        $suffix = filled($cur) ? ' '.$cur : '';
        $amount = number_format($pendingTotal, 2);

        $title = 'أرباح طبيب قيد الانتظار';
        $message = "للطبيب {$doctorName} مجموع أرباح قيد الانتظار يبلغ {$amount}{$suffix}";

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::DOCTOR_EARNING_PENDING,
            'title' => $title,
            'message' => $message,
            'related_type' => Doctor::class,
            'related_id' => $doctorId,
        ]);
    }

    /**
     * بعد دفع مجمّع لعدة سجلات (بدون حدث updated لكل سجل).
     */
    public function notifyDoctorEarningsBatchSettled(int $clinicId, int $doctorId, string $doctorName, int $count, float $totalAmount): void
    {
        if ($clinicId <= 0 || $count <= 0) {
            return;
        }

        $recipients = $this->recipientUserIdsForDoctorEarningManagement($clinicId, $doctorId);
        if ($recipients->isEmpty()) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::DOCTOR_EARNING_BATCH_SETTLED,
            Doctor::class,
            $doctorId,
            now()->subHour(),
            now(),
            $clinicId
        )) {
            return;
        }

        $cur = ClinicSettings::current()->currency ?? '';
        $suffix = filled($cur) ? ' '.$cur : '';
        $total = number_format($totalAmount, 2);

        $title = 'تسوية أرباح طبيب (مجمّعة)';
        $message = "تم تعليم {$count} سجل أرباح للطبيب {$doctorName} كمدفوعة — الإجمالي {$total}{$suffix}";

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::DOCTOR_EARNING_BATCH_SETTLED,
            'title' => $title,
            'message' => $message,
            'related_type' => Doctor::class,
            'related_id' => $doctorId,
        ]);
    }

    public function notifyDoctorEarningPaid(DoctorEarning $earning): void
    {
        $clinicId = (int) $earning->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        $recipients = $this->recipientUserIdsForDoctorEarningManagement($clinicId, (int) $earning->doctor_id);
        if ($recipients->isEmpty()) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::DOCTOR_EARNING_PAID,
            DoctorEarning::class,
            $earning->id,
            now()->subDays(3),
            now(),
            $clinicId
        )) {
            return;
        }

        $earning->loadMissing(['doctor.staff', 'invoice']);
        $doctorName = optional(optional($earning->doctor)->staff)->full_name
            ?? optional($earning->doctor)->full_name
            ?? '—';
        $cur = ClinicSettings::current()->currency ?? '';
        $suffix = filled($cur) ? ' '.$cur : '';
        $amount = number_format((float) $earning->earning_amount, 2);
        $inv = $earning->invoice?->invoice_number ?? ('#'.$earning->invoice_id);

        $title = 'تسوية أرباح طبيب';
        $message = "تم تعليم أرباح الطبيب {$doctorName} كمدفوعة — {$amount}{$suffix} — فاتورة {$inv}";

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::DOCTOR_EARNING_PAID,
            'title' => $title,
            'message' => $message,
            'related_type' => DoctorEarning::class,
            'related_id' => $earning->id,
        ]);
    }

    public function notifyPayrollOutstanding(StaffPayment $payment): void
    {
        if ((float) $payment->remaining_amount <= 0.00001) {
            return;
        }

        $clinicId = (int) $payment->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        $recipients = $this->recipientUserIdsForPayroll($clinicId);
        if ($recipients->isEmpty()) {
            return;
        }

        $type = (float) $payment->paid_amount > 0.00001
            ? AppNotificationType::PAYROLL_PARTIAL
            : AppNotificationType::PAYROLL_UNPAID;

        if ($this->existsRecentDuplicate(
            $type,
            StaffPayment::class,
            $payment->id,
            now()->subHours(6),
            now(),
            $clinicId
        )) {
            return;
        }

        $payment->loadMissing('staff');
        $staffName = $payment->staff?->full_name ?? '—';
        $cur = ClinicSettings::current()->currency ?? '';
        $remaining = number_format((float) $payment->remaining_amount, 2);
        $period = $payment->periodLabel();
        $currencySuffix = filled($cur) ? ' '.$cur : '';
        $currentNote = '';
        if ($payment->period_start && $payment->period_end) {
            $d = now()->toDateString();
            if ($d >= $payment->period_start->toDateString() && $d <= $payment->period_end->toDateString()) {
                $currentNote = __('payroll.notifications.current_period_note');
            }
        }

        if ($type === AppNotificationType::PAYROLL_PARTIAL) {
            $title = __('payroll.notifications.outstanding_partial_title');
            $message = __('payroll.notifications.outstanding_partial_body', [
                'staff' => $staffName,
                'period' => $period,
                'note' => $currentNote,
                'remaining' => $remaining,
                'currency_suffix' => $currencySuffix,
            ]);
        } else {
            $title = __('payroll.notifications.outstanding_unpaid_title');
            $message = __('payroll.notifications.outstanding_unpaid_body', [
                'staff' => $staffName,
                'period' => $period,
                'note' => $currentNote,
                'remaining' => $remaining,
                'currency_suffix' => $currencySuffix,
            ]);
        }

        $this->insertForUsers($recipients, $clinicId, [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_type' => StaffPayment::class,
            'related_id' => $payment->id,
        ]);
    }

    public function notifyPayrollSettled(StaffPayment $payment): void
    {
        $clinicId = (int) $payment->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        $recipients = $this->recipientUserIdsForPayroll($clinicId);
        if ($recipients->isEmpty()) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::PAYROLL_SETTLED,
            StaffPayment::class,
            $payment->id,
            now()->subDays(2),
            now(),
            $clinicId
        )) {
            return;
        }

        $payment->loadMissing('staff');
        $staffName = $payment->staff?->full_name ?? '—';
        $period = $payment->periodLabel();

        $title = __('payroll.notifications.settled_title');
        $message = __('payroll.notifications.settled_body', [
            'staff' => $staffName,
            'period' => $period,
        ]);

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::PAYROLL_SETTLED,
            'title' => $title,
            'message' => $message,
            'related_type' => StaffPayment::class,
            'related_id' => $payment->id,
        ]);
    }

    public function appointmentStartsAt(Appointment $appointment): ?Carbon
    {
        try {
            $date = $appointment->appointment_date?->format('Y-m-d');
            if ($date === null || $appointment->start_time === null) {
                return null;
            }

            return Carbon::parse($date.' '.$appointment->start_time);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * بعد حفظ موعد: إشعارات فورية عند اليوم أو خلال 24 ساعة.
     */
    public function pushForAppointmentIfRelevant(Appointment $appointment): void
    {
        if (! in_array($appointment->status, ['scheduled', 'confirmed'], true)) {
            return;
        }

        $clinicId = (int) $appointment->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        $appointment->loadMissing(['patient', 'doctor']);
        $start = $this->appointmentStartsAt($appointment);
        if ($start === null) {
            return;
        }

        if ($appointment->appointment_date?->isToday()) {
            $this->createAppointmentTodayNotifications($appointment, $clinicId);
        }

        if ($start->isFuture() && $start->lte(now()->addHours(24))) {
            $this->createAppointment24hReminderNotifications($appointment, $clinicId);
        }
    }

    /**
     * أوامر مجدولة: فحص كل المواعيد المجدولة (يُستدعى مع تعطيل نطاق العيادة مؤقتاً من الأمر).
     */
    public function syncScheduledAppointmentAlerts(): void
    {
        Appointment::withoutGlobalScopes()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['patient', 'doctor'])
            ->orderBy('id')
            ->chunkById(200, function (Collection $appointments): void {
                foreach ($appointments as $appointment) {
                    if (! $appointment instanceof Appointment) {
                        continue;
                    }
                    $this->pushForAppointmentIfRelevant($appointment);
                }
            });
    }

    public function createAppointmentTodayNotifications(Appointment $appointment, ?int $clinicId = null): void
    {
        $clinicId ??= (int) $appointment->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::APPOINTMENT_TODAY,
            Appointment::class,
            $appointment->id,
            today()->startOfDay(),
            today()->endOfDay(),
            $clinicId
        )) {
            return;
        }

        $patient = $appointment->patient?->full_name ?? __('common.em_dash');
        $doctor = $appointment->doctor?->full_name ?? __('common.em_dash');
        $time = $appointment->start_time ?? '';
        $title = __('appointments.notify_today_title');
        $message = __('appointments.notify_today_message', [
            'patient' => $patient,
            'doctor' => $doctor,
            'time' => $time,
        ]);

        $this->insertForUsers($this->recipientUserIdsForAppointments($clinicId), $clinicId, [
            'type' => AppNotificationType::APPOINTMENT_TODAY,
            'title' => $title,
            'message' => $message,
            'related_type' => Appointment::class,
            'related_id' => $appointment->id,
        ]);
    }

    public function createAppointment24hReminderNotifications(Appointment $appointment, ?int $clinicId = null): void
    {
        $clinicId ??= (int) $appointment->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::APPOINTMENT_REMINDER_24H,
            Appointment::class,
            $appointment->id,
            now()->subHours(20),
            now(),
            $clinicId
        )) {
            return;
        }

        $start = $this->appointmentStartsAt($appointment);
        if ($start === null) {
            return;
        }

        $patient = $appointment->patient?->full_name ?? __('common.em_dash');
        $doctor = $appointment->doctor?->full_name ?? __('common.em_dash');
        $when = $start->format('d/m/Y H:i');
        $title = __('appointments.notify_reminder_title');
        $message = __('appointments.notify_reminder_message', [
            'patient' => $patient,
            'doctor' => $doctor,
            'when' => $when,
        ]);

        $this->insertForUsers($this->recipientUserIdsForAppointments($clinicId), $clinicId, [
            'type' => AppNotificationType::APPOINTMENT_REMINDER_24H,
            'title' => $title,
            'message' => $message,
            'related_type' => Appointment::class,
            'related_id' => $appointment->id,
        ]);
    }

    public function notifyAppointmentCancelled(Appointment $appointment): void
    {
        $clinicId = (int) $appointment->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::APPOINTMENT_CANCELLED,
            Appointment::class,
            $appointment->id,
            now()->subHours(12),
            now(),
            $clinicId
        )) {
            return;
        }

        $appointment->loadMissing(['patient', 'doctor']);
        $patient = $appointment->patient?->full_name ?? __('common.em_dash');
        $doctor = $appointment->doctor?->full_name ?? __('common.em_dash');
        $date = $appointment->appointment_date?->format('d/m/Y') ?? __('common.em_dash');
        $time = $appointment->start_time ?? '';

        $title = __('appointments.notify_cancelled_title');
        $message = __('appointments.notify_cancelled_message', [
            'date' => $date,
            'time' => $time,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);

        $this->insertForUsers($this->recipientUserIdsForAppointments($clinicId), $clinicId, [
            'type' => AppNotificationType::APPOINTMENT_CANCELLED,
            'title' => $title,
            'message' => $message,
            'related_type' => Appointment::class,
            'related_id' => $appointment->id,
        ]);
    }

    public function notifyOpenInvoiceIfNeeded(Invoice $invoice): void
    {
        if (! in_array($invoice->status, ['unpaid', 'partial'], true)) {
            return;
        }

        $clinicId = (int) $invoice->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::INVOICE_OPEN,
            Invoice::class,
            $invoice->id,
            now()->subHours(4),
            now(),
            $clinicId
        )) {
            return;
        }

        $invoice->loadMissing('patient');
        $patient = $invoice->patient?->full_name ?? '—';
        $statusLabel = $invoice->status === 'partial' ? __('common.partial') : __('common.unpaid');
        $title = __('payments.notifications.invoice_open_title');
        $message = __('payments.notifications.invoice_open_body', [
            'invoice' => $invoice->invoice_number,
            'patient' => $patient,
            'status' => $statusLabel,
            'total' => number_format((float) $invoice->total, 2),
        ]);

        $this->insertForUsers($this->recipientUserIdsForFinance($clinicId), $clinicId, [
            'type' => AppNotificationType::INVOICE_OPEN,
            'title' => $title,
            'message' => $message,
            'related_type' => Invoice::class,
            'related_id' => $invoice->id,
        ]);
    }

    /**
     * @deprecated استخدم notifyOpenInvoiceIfNeeded
     */
    public function notifyNewOpenInvoice(Invoice $invoice): void
    {
        $this->notifyOpenInvoiceIfNeeded($invoice);
    }

    public function notifyInvoiceFullyPaid(Invoice $invoice): void
    {
        $clinicId = (int) $invoice->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::INVOICE_PAID,
            Invoice::class,
            $invoice->id,
            now()->subDays(2),
            now(),
            $clinicId
        )) {
            return;
        }

        $invoice->loadMissing('patient');
        $patient = $invoice->patient?->full_name ?? '—';
        $title = __('payments.notifications.invoice_paid_title');
        $message = __('payments.notifications.invoice_paid_body', [
            'invoice' => $invoice->invoice_number,
            'patient' => $patient,
            'total' => number_format((float) $invoice->total, 2),
        ]);

        $this->insertForUsers($this->recipientUserIdsForFinance($clinicId), $clinicId, [
            'type' => AppNotificationType::INVOICE_PAID,
            'title' => $title,
            'message' => $message,
            'related_type' => Invoice::class,
            'related_id' => $invoice->id,
        ]);
    }

    public function notifyPaymentRecorded(Payment $payment, Invoice $invoice): void
    {
        $clinicId = (int) ($invoice->clinic_id ?: $payment->clinic_id);
        if ($clinicId <= 0) {
            return;
        }

        $invoice->loadMissing('patient');
        $patient = $invoice->patient?->full_name ?? '—';
        $title = __('payments.notifications.payment_recorded_title');
        $message = __('payments.notifications.payment_recorded_body', [
            'amount' => number_format((float) $payment->amount, 2),
            'invoice' => $invoice->invoice_number,
            'patient' => $patient,
        ]);

        $this->insertForUsers($this->recipientUserIdsForFinance($clinicId), $clinicId, [
            'type' => AppNotificationType::PAYMENT_RECORDED,
            'title' => $title,
            'message' => $message,
            'related_type' => Payment::class,
            'related_id' => $payment->id,
        ]);
    }

    public function notifyVisitRecorded(Visit $visit): void
    {
        $clinicId = (int) $visit->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::VISIT_RECORDED,
            Visit::class,
            $visit->id,
            now()->subHours(2),
            now(),
            $clinicId
        )) {
            return;
        }

        $visit->loadMissing(['patient', 'doctor']);
        $patient = $visit->patient?->full_name ?? '—';
        $doctor = $visit->doctor?->full_name ?? '—';
        $date = $visit->visit_date?->format('d/m/Y') ?? '—';

        $title = 'زيارة جديدة';
        $message = "تسجيل زيارة — المريض {$patient} — الطبيب {$doctor} — التاريخ {$date}";

        $this->insertForUsers($this->recipientUserIdsForVisits($clinicId), $clinicId, [
            'type' => AppNotificationType::VISIT_RECORDED,
            'title' => $title,
            'message' => $message,
            'related_type' => Visit::class,
            'related_id' => $visit->id,
        ]);
    }

    public function notifyPatientRegistered(Patient $patient): void
    {
        $clinicId = (int) $patient->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::PATIENT_REGISTERED,
            Patient::class,
            $patient->id,
            now()->subHours(2),
            now(),
            $clinicId
        )) {
            return;
        }

        $title = 'مريض جديد';
        $message = "تسجيل مريض: {$patient->full_name} — ملف {$patient->file_number}";

        $this->insertForUsers($this->recipientUserIdsForPatients($clinicId), $clinicId, [
            'type' => AppNotificationType::PATIENT_REGISTERED,
            'title' => $title,
            'message' => $message,
            'related_type' => Patient::class,
            'related_id' => $patient->id,
        ]);
    }

    public function notifyExpenseRecorded(Expense $expense): void
    {
        $clinicId = (int) $expense->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::EXPENSE_RECORDED,
            Expense::class,
            $expense->id,
            now()->subHours(2),
            now(),
            $clinicId
        )) {
            return;
        }

        $expense->loadMissing('category');
        $cat = $expense->category?->name ?? '—';
        $amount = number_format((float) $expense->amount, 2);
        $suffix = '';
        $cur = ClinicSettings::current()->currency ?? '';
        if (filled($cur)) {
            $suffix = ' '.$cur;
        }
        $title = __('expenses.notifications.recorded_title');
        $message = __('expenses.notifications.recorded_body', [
            'title' => $expense->title,
            'category' => $cat,
            'amount' => $amount.$suffix,
        ]);

        $this->insertForUsers($this->recipientUserIdsForExpenses($clinicId), $clinicId, [
            'type' => AppNotificationType::EXPENSE_RECORDED,
            'title' => $title,
            'message' => $message,
            'related_type' => Expense::class,
            'related_id' => $expense->id,
        ]);
    }

    public function notifySubscriptionExpiring(Clinic $clinic, int $daysRemaining): void
    {
        $clinicId = (int) $clinic->id;
        $recipients = $this->recipientUserIdsForBilling($clinicId);
        if ($recipients->isEmpty()) {
            return;
        }

        $expires = $clinic->subscription_expires_at?->format('d/m/Y H:i') ?? '—';

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::SUBSCRIPTION_EXPIRING,
            'title' => __('saas.notification_expiring_title', ['days' => $daysRemaining]),
            'message' => __('saas.notification_expiring_body', [
                'clinic' => $clinic->name,
                'days' => $daysRemaining,
                'expires' => $expires,
            ]),
            'related_type' => Clinic::class,
            'related_id' => $clinicId,
        ]);
    }

    /**
     * @return Collection<int, int>
     */
    public function recipientUserIdsForBilling(int $clinicId): Collection
    {
        $clinic = Clinic::query()->find($clinicId);
        $ownerId = $clinic?->owner_id;

        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) use ($ownerId) {
                $q->role('admin')
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_BILLING));
                if ($ownerId) {
                    $q->orWhere('id', $ownerId);
                }
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    public function notifyPlatformClinicSubscription(Clinic $clinic, Subscription $subscription): void
    {
        $recipients = $this->recipientUserIdsForPlatformSuperAdmins();
        if ($recipients->isEmpty()) {
            return;
        }

        $clinicId = (int) $clinic->id;
        $status = (string) ($subscription->stripe_status ?? '');
        $title = 'اشتراك عيادة';
        $message = "العيادة «{$clinic->name}» — حالة Stripe: {$status}";

        if ($this->existsRecentDuplicate(
            AppNotificationType::PLATFORM_CLINIC_SUBSCRIPTION,
            Subscription::class,
            (int) $subscription->id,
            now()->subHour(),
            now(),
            $clinicId
        )) {
            return;
        }

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::PLATFORM_CLINIC_SUBSCRIPTION,
            'title' => $title,
            'message' => $message,
            'related_type' => Subscription::class,
            'related_id' => (int) $subscription->id,
        ]);
    }

    /**
     * إشعار لمديري المنصّة (super_admin) عند إجراء يدوي من لوحة المنصّة — عيادة، تفعيل، دفعة، إلخ.
     */
    public function notifyPlatformClinicManaged(Clinic $clinic, string $title, string $message): void
    {
        $recipients = $this->recipientUserIdsForPlatformSuperAdmins();
        if ($recipients->isEmpty()) {
            return;
        }

        $clinicId = (int) $clinic->id;
        if ($clinicId <= 0) {
            return;
        }

        $this->insertForUsers($recipients, $clinicId, [
            'type' => AppNotificationType::PLATFORM_CLINIC_MANAGED,
            'title' => $title,
            'message' => $message,
            'related_type' => Clinic::class,
            'related_id' => $clinicId,
        ]);
    }

    public function notifyExportReady(User $user, string $filename, string $storagePath): void
    {
        $clinicId = (int) ($user->clinic_id ?? 0);
        $downloadUrl = route('exports.download', ['path' => basename($storagePath)]);

        AppNotification::query()->create([
            'user_id' => $user->id,
            'clinic_id' => $clinicId > 0 ? $clinicId : null,
            'type' => AppNotificationType::EXPORT_READY,
            'title' => __('exports.ready_title'),
            'message' => __('exports.ready_message', ['file' => $filename]).' '.$downloadUrl,
            'related_type' => null,
            'related_id' => null,
            'is_read' => false,
        ]);
    }

    /**
     * @param  array{type: string, title: string, message: string, related_type: string|null, related_id: int|null}  $payload
     */
    private function insertForUsers(Collection $userIds, int $clinicId, array $payload): void
    {
        if ($userIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $userIds->map(fn (int $uid) => array_merge($payload, [
            'user_id' => $uid,
            'clinic_id' => $clinicId,
            'is_read' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]))->all();

        DB::table('app_notifications')->insert($rows);

        app(PushNotificationDispatcher::class)->dispatchToUsers(
            $userIds,
            (string) $payload['title'],
            (string) $payload['message'],
            array_filter([
                'type' => (string) $payload['type'],
                'related_type' => $payload['related_type'] ? class_basename((string) $payload['related_type']) : '',
                'related_id' => $payload['related_id'] !== null ? (string) $payload['related_id'] : '',
            ]),
        );
    }

    public function recipientUserIdsForInventory(int $clinicId): Collection
    {
        return $this->usersInClinic($clinicId)
            ->where(function (Builder $q) {
                $q->role(['admin', 'receptionist', 'accountant'])
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::MANAGE_INVENTORY))
                    ->orWhere(fn (Builder $p) => $p->permission(ClinicPermissions::VIEW_INVENTORY));
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    public function notifyInventoryLowStock(InventoryItem $item): void
    {
        $clinicId = (int) $item->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::INVENTORY_LOW_STOCK,
            InventoryItem::class,
            $item->id,
            now()->subDay(),
            now(),
            $clinicId
        )) {
            return;
        }

        $this->insertForUsers($this->recipientUserIdsForInventory($clinicId), $clinicId, [
            'type' => AppNotificationType::INVENTORY_LOW_STOCK,
            'title' => __('inventory.notifications.low_stock_title'),
            'message' => __('inventory.notifications.low_stock_body', [
                'name' => $item->name,
                'qty' => number_format((float) $item->quantity_on_hand, 3),
                'min' => number_format((float) $item->minimum_quantity, 3),
            ]),
            'related_type' => InventoryItem::class,
            'related_id' => $item->id,
        ]);
    }

    public function notifyInventoryExpiring(InventoryItem $item): void
    {
        $clinicId = (int) $item->clinic_id;
        if ($clinicId <= 0 || $item->expiry_date === null) {
            return;
        }

        if ($this->existsRecentDuplicate(
            AppNotificationType::INVENTORY_EXPIRING,
            InventoryItem::class,
            $item->id,
            now()->subDays(3),
            now(),
            $clinicId
        )) {
            return;
        }

        $this->insertForUsers($this->recipientUserIdsForInventory($clinicId), $clinicId, [
            'type' => AppNotificationType::INVENTORY_EXPIRING,
            'title' => __('inventory.notifications.expiring_title'),
            'message' => __('inventory.notifications.expiring_body', [
                'name' => $item->name,
                'date' => $item->expiry_date->format('d/m/Y'),
            ]),
            'related_type' => InventoryItem::class,
            'related_id' => $item->id,
        ]);
    }

    public function notifyInventoryConsumptionFailed(Visit $visit, string $procedureName, string $reason): void
    {
        $clinicId = (int) $visit->clinic_id;
        if ($clinicId <= 0) {
            return;
        }

        $this->insertForUsers($this->recipientUserIdsForInventory($clinicId), $clinicId, [
            'type' => AppNotificationType::INVENTORY_CONSUMPTION_FAILED,
            'title' => __('inventory.notifications.consumption_failed_title'),
            'message' => __('inventory.notifications.consumption_failed_body', [
                'procedure' => $procedureName,
                'reason' => $reason,
            ]),
            'related_type' => Visit::class,
            'related_id' => $visit->id,
        ]);
    }

    private function existsRecentDuplicate(
        string $type,
        string $relatedType,
        int $relatedId,
        Carbon $from,
        Carbon $to,
        ?int $clinicId = null
    ): bool {
        $q = AppNotification::withoutGlobalScopes()
            ->where('type', $type)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->whereBetween('created_at', [$from, $to]);

        if ($clinicId !== null) {
            $q->where('clinic_id', $clinicId);
        }

        return $q->exists();
    }
}
