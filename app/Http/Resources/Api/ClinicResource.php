<?php

namespace App\Http\Resources\Api;

use App\Support\PlanDisplay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Clinic */
final class ClinicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $plan = $this->relationLoaded('plan') ? $this->plan : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'subscription_status' => $this->subscription_status,
            'subscription_expires_at' => $this->subscription_expires_at?->toIso8601String(),
            'plan' => $plan ? [
                'slug' => $plan->slug,
                'name' => PlanDisplay::localizedName($plan),
            ] : null,
        ];
    }
}
