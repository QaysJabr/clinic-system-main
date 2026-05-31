<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * خطط الاشتراك — مبنية على دراسة جدوى SaaS لعيادات صغيرة/متوسطة (MENA + عالمي).
 *
 * تسعير مرجعي: ClinicMaster / SimplePractice فئة $40–150/شهر لعيادة واحدة.
 * خصم سنوي ≈ شهرين مجاناً (≈17%).
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price_monthly' => 39.00,
                'price_yearly' => 390.00,
                'max_patients' => 400,
                'max_users' => 4,
                'trial_days' => 7,
                'features' => [
                    'feat_appointments',
                    'feat_visits',
                    'feat_patients_emr',
                    'feat_invoices',
                    'feat_payments',
                    'feat_dashboard',
                    'feat_roles_basic',
                    'feat_reports_basic',
                    'feat_help_support',
                ],
                'stripe_price_id' => env('STRIPE_PRICE_BASIC_MONTHLY'),
                'stripe_price_yearly_id' => env('STRIPE_PRICE_BASIC_YEARLY'),
                'sort_order' => 10,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_monthly' => 79.00,
                'price_yearly' => 790.00,
                'max_patients' => 2000,
                'max_users' => 10,
                'trial_days' => 7,
                'features' => [
                    'feat_all_basic',
                    'feat_inventory',
                    'feat_expenses',
                    'feat_advanced_reports',
                    'feat_excel_export',
                    'feat_internal_chat',
                    'feat_doctor_schedules',
                    'feat_receivables',
                    'feat_attachments',
                    'feat_multi_doctor',
                ],
                'stripe_price_id' => env('STRIPE_PRICE_PRO_MONTHLY'),
                'stripe_price_yearly_id' => env('STRIPE_PRICE_PRO_YEARLY'),
                'sort_order' => 20,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price_monthly' => 129.00,
                'price_yearly' => 1290.00,
                'max_patients' => null,
                'max_users' => 25,
                'trial_days' => 14,
                'features' => [
                    'feat_all_pro',
                    'feat_payroll',
                    'feat_doctor_earnings',
                    'feat_auto_backup',
                    'feat_advanced_permissions',
                    'feat_audit_trail',
                    'feat_priority_support',
                    'feat_onboarding',
                    'feat_unlimited_reports',
                ],
                'stripe_price_id' => env('STRIPE_PRICE_PREMIUM_MONTHLY'),
                'stripe_price_yearly_id' => env('STRIPE_PRICE_PREMIUM_YEARLY'),
                'sort_order' => 30,
            ],
        ];

        foreach ($plans as $row) {
            Plan::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['is_active' => true]
            );
        }
    }
}
