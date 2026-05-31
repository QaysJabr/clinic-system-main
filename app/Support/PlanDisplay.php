<?php

namespace App\Support;

use App\Models\Plan;

final class PlanDisplay
{
    /**
     * @param  Plan|\stdClass|null  $plan  Eloquent plan, or a plain object (e.g. Blade fallback cards with translated `name` / optional `slug`).
     */
    public static function localizedName(Plan|\stdClass|null $plan, ?string $fallback = null): string
    {
        if ($plan === null) {
            return $fallback ?? __('common.em_dash');
        }

        if ($plan instanceof Plan) {
            $key = 'saas.plan_name_'.$plan->slug;

            return __($key) !== $key ? __($key) : ($plan->name ?: $fallback ?? __('common.em_dash'));
        }

        $slug = isset($plan->slug) ? trim((string) $plan->slug) : '';
        if ($slug !== '') {
            $key = 'saas.plan_name_'.$slug;
            $translated = __($key);

            return $translated !== $key ? $translated : (trim((string) ($plan->name ?? '')) !== '' ? (string) $plan->name : ($fallback ?? __('common.em_dash')));
        }

        $name = isset($plan->name) ? trim((string) $plan->name) : '';

        return $name !== '' ? $name : ($fallback ?? __('common.em_dash'));
    }

    public static function localizedFeature(string $line): string
    {
        $trimmed = trim($line);

        $map = [
            'كل ميزات Basic' => 'saas.feature_all_basic',
            'كل ميزات Pro' => 'saas.feature_all_pro',
            'All Basic features' => 'saas.feature_all_basic',
            'All Pro features' => 'saas.feature_all_pro',
        ];

        if (isset($map[$trimmed])) {
            $translated = __($map[$trimmed]);

            return $translated !== $map[$trimmed] ? $translated : $trimmed;
        }

        if (str_starts_with($trimmed, 'feat_')) {
            $key = 'saas.'.$trimmed;
            $translated = __($key);

            return $translated !== $key ? $translated : $trimmed;
        }

        return $trimmed;
    }

    public static function planFitLabel(Plan|\stdClass|null $plan): ?string
    {
        if ($plan === null) {
            return null;
        }

        $slug = $plan instanceof Plan ? $plan->slug : (isset($plan->slug) ? (string) $plan->slug : '');
        $slug = strtolower(trim($slug));

        if ($slug !== '') {
            $key = 'saas.plan_fit_'.$slug;
            $translated = __($key);

            return $translated !== $key ? $translated : null;
        }

        $name = strtolower((string) ($plan->name ?? ''));
        if (str_contains($name, 'pro') || str_contains($name, 'احتراف')) {
            return __('saas.plan_fit_pro');
        }
        if (str_contains($name, 'premium') || str_contains($name, 'ممي')) {
            return __('saas.plan_fit_premium');
        }
        if (str_contains($name, 'basic') || str_contains($name, 'أساس')) {
            return __('saas.plan_fit_basic');
        }

        return null;
    }

    public static function patientsLimitLabel(?int $max): string
    {
        if ($max === null) {
            return __('saas.limit_patients_unlimited');
        }

        return __('saas.limit_patients_count', ['count' => number_format($max)]);
    }

    public static function usersLimitLabel(?int $max): string
    {
        if ($max === null) {
            return __('saas.limit_users_unlimited');
        }

        return __('saas.limit_users_count', ['count' => number_format($max)]);
    }

    public static function trialDaysLabel(int $days): string
    {
        return __('saas.limit_trial_days', ['days' => $days]);
    }

    public static function yearlySavingsPercent(float $monthly, float $yearly): ?int
    {
        if ($monthly <= 0 || $yearly <= 0) {
            return null;
        }

        $full = $monthly * 12;

        return (int) round((1 - ($yearly / $full)) * 100);
    }
}
