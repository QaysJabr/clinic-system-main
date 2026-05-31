<?php

namespace App\Support;

/**
 * Locale-aware labels for audit log filters and tables.
 */
final class AuditLogLabels
{
    /**
     * @return array<string, string>
     */
    public static function modules(): array
    {
        return [
            'auth' => __('audit.module_auth'),
            'patients' => __('audit.module_patients'),
            'doctors' => __('audit.module_doctors'),
            'appointments' => __('audit.module_appointments'),
            'visits' => __('audit.module_visits'),
            'invoices' => __('audit.module_invoices'),
            'payments' => __('audit.module_payments'),
            'attachments' => __('audit.module_attachments'),
            'users' => __('audit.module_users'),
            'settings' => __('audit.module_settings'),
            'profile' => __('audit.module_profile'),
            'notifications' => __('audit.module_notifications'),
            'reports' => __('audit.module_reports'),
            'backups' => __('audit.module_backups'),
            'staff_compensation_profiles' => __('audit.module_staff_compensation_profiles'),
            'staff_payments' => __('audit.module_staff_payments'),
            'payroll_runs' => __('audit.module_payroll_runs'),
            'expenses' => __('audit.module_expenses'),
            'staff' => __('audit.module_staff'),
            'services' => __('audit.module_services'),
            'clinics' => __('audit.module_clinics'),
            'doctor_earnings' => __('audit.module_doctor_earnings'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function actions(): array
    {
        return [
            'create' => __('audit.action_create'),
            'update' => __('audit.action_update'),
            'delete' => __('audit.action_delete'),
            'payment' => __('audit.action_payment'),
            'login' => __('audit.action_login'),
            'logout' => __('audit.action_logout'),
            'download' => __('audit.action_download'),
            'export' => __('audit.action_export'),
            'request' => __('audit.action_request'),
            'confirm' => __('audit.action_confirm'),
            'staff_payment' => __('audit.action_staff_payment'),
            'payroll_run_generate' => __('audit.action_payroll_run_generate'),
        ];
    }
}
