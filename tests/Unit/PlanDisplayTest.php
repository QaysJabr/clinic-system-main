<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Support\PlanDisplay;
use Tests\TestCase;

class PlanDisplayTest extends TestCase
{
    public function test_localized_feature_maps_legacy_english_lines(): void
    {
        app()->setLocale('ar');

        $this->assertSame(
            __('saas.feature_all_pro'),
            PlanDisplay::localizedFeature('كل ميزات Pro')
        );
    }

    public function test_localized_name_uses_slug_translation(): void
    {
        app()->setLocale('ar');

        $plan = new Plan(['name' => 'Pro', 'slug' => 'pro']);

        $this->assertSame(__('saas.plan_name_pro'), PlanDisplay::localizedName($plan));
    }

    public function test_localized_name_accepts_plain_object_fallback(): void
    {
        app()->setLocale('ar');

        $card = (object) ['name' => __('saas.pricing_basic')];

        $this->assertSame(__('saas.pricing_basic'), PlanDisplay::localizedName($card));
    }

    public function test_localized_feature_maps_feat_keys(): void
    {
        app()->setLocale('ar');

        $this->assertSame(
            __('saas.feat_internal_chat'),
            PlanDisplay::localizedFeature('feat_internal_chat')
        );
    }

    public function test_yearly_savings_percent(): void
    {
        $this->assertSame(17, PlanDisplay::yearlySavingsPercent(39.0, 390.0));
    }
}
