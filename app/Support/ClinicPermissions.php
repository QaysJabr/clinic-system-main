<?php

namespace App\Support;

/**
 * أسماء الصلاحيات كما تُخزَّن في جدول permissions (يجب أن تطابق الـ Seeder والمسارات).
 */
final class ClinicPermissions
{
    public const MANAGE_USERS = 'manage users';

    public const VIEW_DASHBOARD = 'view dashboard';

    public const MANAGE_PATIENTS = 'manage patients';

    public const MANAGE_DOCTORS = 'manage doctors';

    public const MANAGE_APPOINTMENTS = 'manage appointments';

    public const MANAGE_VISITS = 'manage visits';

    public const MANAGE_INVOICES = 'manage invoices';

    public const MANAGE_PAYMENTS = 'manage payments';

    public const VIEW_REPORTS = 'view reports';

    public const VIEW_AUDIT_LOGS = 'view audit logs';

    public const MANAGE_SETTINGS = 'manage settings';

    public const VIEW_NOTIFICATIONS = 'view notifications';

    public const MANAGE_ATTACHMENTS = 'manage attachments';

    public const VIEW_ATTACHMENTS = 'view attachments';

    public const MANAGE_BACKUPS = 'manage backups';

    public const MANAGE_EXPENSES = 'manage expenses';

    public const MANAGE_EXPENSE_CATEGORIES = 'manage expense categories';

    public const MANAGE_STAFF_PAYROLL = 'manage staff payroll';

    /** تسجيل دفعات الرواتب اليدوية (الموظفون في جدول staff). */
    public const MANAGE_PAYROLL = 'manage payroll';

    public const MANAGE_STAFF = 'manage staff';

    public const VIEW_DOCTOR_EARNINGS = 'view doctor earnings';

    public const MANAGE_DOCTOR_EARNINGS = 'manage doctor earnings';

    public const MANAGE_INVENTORY = 'manage inventory';

    public const VIEW_INVENTORY = 'view inventory';

    /** SaaS clinic billing: checkout, cancel, Stripe portal (owner always has this implicitly). */
    public const MANAGE_BILLING = 'manage billing';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::MANAGE_USERS,
            self::MANAGE_SETTINGS,
            self::VIEW_NOTIFICATIONS,
            self::MANAGE_ATTACHMENTS,
            self::VIEW_ATTACHMENTS,
            self::VIEW_DASHBOARD,
            self::MANAGE_PATIENTS,
            self::MANAGE_DOCTORS,
            self::MANAGE_APPOINTMENTS,
            self::MANAGE_VISITS,
            self::MANAGE_INVOICES,
            self::MANAGE_PAYMENTS,
            self::VIEW_REPORTS,
            self::VIEW_AUDIT_LOGS,
            self::MANAGE_BACKUPS,
            self::MANAGE_EXPENSES,
            self::MANAGE_EXPENSE_CATEGORIES,
            self::MANAGE_STAFF_PAYROLL,
            self::MANAGE_PAYROLL,
            self::MANAGE_STAFF,
            self::VIEW_DOCTOR_EARNINGS,
            self::MANAGE_DOCTOR_EARNINGS,
            self::MANAGE_INVENTORY,
            self::VIEW_INVENTORY,
            self::MANAGE_BILLING,
        ];
    }
}
