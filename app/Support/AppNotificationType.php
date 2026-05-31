<?php

namespace App\Support;

/**
 * قيم حقل type في app_notifications + تسميات الواجهة.
 */
final class AppNotificationType
{
    public const APPOINTMENT_TODAY = 'appointment_today';

    public const APPOINTMENT_REMINDER_24H = 'appointment_reminder_24h';

    public const APPOINTMENT_CANCELLED = 'appointment_cancelled';

    public const INVOICE_OPEN = 'invoice_open';

    public const INVOICE_PAID = 'invoice_paid';

    public const PAYMENT_RECORDED = 'payment_recorded';

    public const DOCTOR_EARNING_CREATED = 'doctor_earning_created';

    public const DOCTOR_EARNING_PENDING = 'doctor_earning_pending';

    public const DOCTOR_EARNING_PAID = 'doctor_earning_paid';

    public const DOCTOR_EARNING_BATCH_SETTLED = 'doctor_earning_batch_settled';

    public const PAYROLL_UNPAID = 'payroll_unpaid';

    public const PAYROLL_PARTIAL = 'payroll_partial';

    public const PAYROLL_SETTLED = 'payroll_settled';

    public const VISIT_RECORDED = 'visit_recorded';

    public const PATIENT_REGISTERED = 'patient_registered';

    public const EXPENSE_RECORDED = 'expense_recorded';

    public const INVENTORY_LOW_STOCK = 'inventory_low_stock';

    public const INVENTORY_EXPIRING = 'inventory_expiring';

    public const INVENTORY_CONSUMPTION_FAILED = 'inventory_consumption_failed';

    public const SUBSCRIPTION_EXPIRING = 'subscription_expiring';

    public const PLATFORM_CLINIC_SUBSCRIPTION = 'platform_clinic_subscription';

    /** إجراءات يدوية من لوحة المنصّة (تفعيل، دفعة، إلخ) */
    public const PLATFORM_CLINIC_MANAGED = 'platform_clinic_managed';

    public const EXPORT_READY = 'export_ready';

    public const PLATFORM_DATABASE_BACKUP = 'platform_database_backup';

    /**
     * أنواع مخصّصة لمدير المنصّة (super_admin) — لا تظهر في واجهة العيادة.
     *
     * @return list<string>
     */
    public static function platformTypes(): array
    {
        return [
            self::PLATFORM_CLINIC_SUBSCRIPTION,
            self::PLATFORM_CLINIC_MANAGED,
            self::PLATFORM_DATABASE_BACKUP,
        ];
    }

    public static function isPlatformType(string $type): bool
    {
        return in_array($type, self::platformTypes(), true);
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::APPOINTMENT_TODAY => __('notifications.type_appointment_today'),
            self::APPOINTMENT_REMINDER_24H => __('notifications.type_appointment_reminder_24h'),
            self::APPOINTMENT_CANCELLED => __('notifications.type_appointment_cancelled'),
            self::INVOICE_OPEN => __('notifications.type_invoice_open'),
            self::INVOICE_PAID => __('notifications.type_invoice_paid'),
            self::PAYMENT_RECORDED => __('notifications.type_payment_recorded'),
            self::DOCTOR_EARNING_CREATED => __('notifications.type_doctor_earning_created'),
            self::DOCTOR_EARNING_PENDING => __('notifications.type_doctor_earning_pending'),
            self::DOCTOR_EARNING_PAID => __('notifications.type_doctor_earning_paid'),
            self::DOCTOR_EARNING_BATCH_SETTLED => __('notifications.type_doctor_earning_batch_settled'),
            self::PAYROLL_UNPAID => __('notifications.type_payroll_unpaid'),
            self::PAYROLL_PARTIAL => __('notifications.type_payroll_partial'),
            self::PAYROLL_SETTLED => __('notifications.type_payroll_settled'),
            self::VISIT_RECORDED => __('notifications.type_visit_recorded'),
            self::PATIENT_REGISTERED => __('notifications.type_patient_registered'),
            self::EXPENSE_RECORDED => __('notifications.type_expense_recorded'),
            self::INVENTORY_LOW_STOCK => __('notifications.type_inventory_low_stock'),
            self::INVENTORY_EXPIRING => __('notifications.type_inventory_expiring'),
            self::INVENTORY_CONSUMPTION_FAILED => __('notifications.type_inventory_consumption_failed'),
            self::SUBSCRIPTION_EXPIRING => __('notifications.type_subscription_expiring'),
            self::PLATFORM_CLINIC_SUBSCRIPTION => __('notifications.type_platform_clinic_subscription'),
            self::PLATFORM_CLINIC_MANAGED => __('notifications.type_platform_clinic_managed'),
            self::EXPORT_READY => __('notifications.type_export_ready'),
            self::PLATFORM_DATABASE_BACKUP => __('notifications.type_platform_database_backup'),
        ];
    }

    public static function label(string $type): string
    {
        return self::labels()[$type] ?? $type;
    }

    /**
     * @return array<string, string>
     */
    public static function pillClasses(): array
    {
        return [
            self::APPOINTMENT_TODAY => 'bg-sky-50 text-sky-900 dark:bg-sky-950/40 dark:text-sky-200',
            self::APPOINTMENT_REMINDER_24H => 'bg-sky-50 text-sky-900 dark:bg-sky-950/40 dark:text-sky-200',
            self::APPOINTMENT_CANCELLED => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            self::INVOICE_OPEN => 'bg-amber-50 text-amber-900 dark:bg-amber-950/35 dark:text-amber-200',
            self::INVOICE_PAID => 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-200',
            self::PAYMENT_RECORDED => 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-200',
            self::DOCTOR_EARNING_CREATED => 'bg-cyan-50 text-cyan-900 dark:bg-cyan-950/40 dark:text-cyan-200',
            self::DOCTOR_EARNING_PENDING => 'bg-amber-50 text-amber-900 dark:bg-amber-950/35 dark:text-amber-200',
            self::DOCTOR_EARNING_PAID => 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-200',
            self::DOCTOR_EARNING_BATCH_SETTLED => 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-200',
            self::PAYROLL_UNPAID => 'bg-rose-50 text-rose-900 dark:bg-rose-950/40 dark:text-rose-200',
            self::PAYROLL_PARTIAL => 'bg-amber-50 text-amber-900 dark:bg-amber-950/35 dark:text-amber-200',
            self::PAYROLL_SETTLED => 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-200',
            self::VISIT_RECORDED => 'bg-indigo-50 text-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200',
            self::PATIENT_REGISTERED => 'bg-blue-50 text-blue-900 dark:bg-blue-950/40 dark:text-blue-200',
            self::EXPENSE_RECORDED => 'bg-orange-50 text-orange-900 dark:bg-orange-950/35 dark:text-orange-200',
            self::INVENTORY_LOW_STOCK => 'bg-amber-50 text-amber-900 dark:bg-amber-950/35 dark:text-amber-200',
            self::INVENTORY_EXPIRING => 'bg-rose-50 text-rose-900 dark:bg-rose-950/40 dark:text-rose-200',
            self::INVENTORY_CONSUMPTION_FAILED => 'bg-red-50 text-red-900 dark:bg-red-950/40 dark:text-red-200',
            self::SUBSCRIPTION_EXPIRING => 'bg-amber-50 text-amber-900 dark:bg-amber-950/35 dark:text-amber-200',
            self::PLATFORM_CLINIC_SUBSCRIPTION => 'bg-violet-50 text-violet-900 dark:bg-violet-950/40 dark:text-violet-200',
            self::PLATFORM_CLINIC_MANAGED => 'bg-violet-50 text-violet-900 dark:bg-violet-950/40 dark:text-violet-200',
            self::EXPORT_READY => 'bg-teal-50 text-teal-900 dark:bg-teal-950/40 dark:text-teal-200',
            self::PLATFORM_DATABASE_BACKUP => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-200',
        ];
    }

    public static function pillClass(string $type): string
    {
        return self::pillClasses()[$type] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200';
    }
}
