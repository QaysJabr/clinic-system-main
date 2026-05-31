<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Plan */
final class PlatformPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price_monthly' => (string) $this->price_monthly,
            'price_yearly' => (string) $this->price_yearly,
            'display_monthly' => $this->displayMonthly(),
            'display_yearly' => $this->displayYearly(),
            'max_patients' => $this->max_patients,
            'max_users' => $this->max_users,
            'features' => $this->features ?? [],
            'trial_days' => $this->trial_days,
            'stripe_price_id' => $this->stripe_price_id,
            'stripe_price_yearly_id' => $this->stripe_price_yearly_id,
            'is_active' => (bool) $this->is_active,
            'sort_order' => $this->sort_order,
            'clinics_count' => (int) ($this->clinics_count ?? 0),
        ];
    }
}
