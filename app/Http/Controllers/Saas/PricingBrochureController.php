<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Support\PlanDisplay;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class PricingBrochureController extends Controller
{
    public function __invoke(): Response
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $cards = $plans->map(function (Plan $plan) {
            $monthly = (float) $plan->price_monthly;
            $yearly = (float) $plan->price_yearly;

            return [
                'plan' => $plan,
                'name' => PlanDisplay::localizedName($plan),
                'patients' => PlanDisplay::patientsLimitLabel($plan->max_patients),
                'users' => PlanDisplay::usersLimitLabel($plan->max_users),
                'trial' => $plan->trial_days > 0
                    ? PlanDisplay::trialDaysLabel((int) $plan->trial_days)
                    : null,
                'monthly' => $plan->displayMonthly(),
                'yearly' => $plan->displayYearly(),
                'yearly_save' => PlanDisplay::yearlySavingsPercent($monthly, $yearly),
                'features' => collect($plan->features ?? [])
                    ->map(fn (string $f) => PlanDisplay::localizedFeature($f))
                    ->values()
                    ->all(),
                'is_popular' => $plan->slug === 'pro',
            ];
        });

        $compareRows = [
            ['label' => __('saas.brochure_row_patients'), 'basic' => '400', 'pro' => '2,000', 'premium' => __('saas.limit_patients_unlimited')],
            ['label' => __('saas.brochure_row_users'), 'basic' => '4', 'pro' => '10', 'premium' => '25'],
            ['label' => __('saas.brochure_row_trial'), 'basic' => '7', 'pro' => '7', 'premium' => '14'],
            ['label' => __('saas.brochure_row_appointments'), 'basic' => true, 'pro' => true, 'premium' => true],
            ['label' => __('saas.brochure_row_inventory'), 'basic' => false, 'pro' => true, 'premium' => true],
            ['label' => __('saas.brochure_row_excel'), 'basic' => false, 'pro' => true, 'premium' => true],
            ['label' => __('saas.brochure_row_chat'), 'basic' => false, 'pro' => true, 'premium' => true],
            ['label' => __('saas.brochure_row_payroll'), 'basic' => false, 'pro' => false, 'premium' => true],
            ['label' => __('saas.brochure_row_doctor_earnings'), 'basic' => false, 'pro' => false, 'premium' => true],
            ['label' => __('saas.brochure_row_backup'), 'basic' => false, 'pro' => false, 'premium' => true],
            ['label' => __('saas.brochure_row_permissions'), 'basic' => false, 'pro' => false, 'premium' => true],
            ['label' => __('saas.brochure_row_priority'), 'basic' => false, 'pro' => false, 'premium' => true],
        ];

        $pdf = Pdf::loadView('saas.pdf.pricing-brochure', [
            'cards' => $cards,
            'compareRows' => $compareRows,
            'pricingUrl' => route('saas.pricing'),
            'whatsapp' => (string) config('saas.support.whatsapp', ''),
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('ClinicSystem-Pricing-'.now()->format('Y-m-d').'.pdf');
    }
}
