<?php

namespace App\Http\Resources\Api;

use App\Support\PlatformClinicPresentation;
use App\Support\PlatformStripePresentation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Clinic */
final class PlatformClinicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tier = PlatformClinicPresentation::subscriptionTier($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => (bool) $this->is_active,
            'subscription_status' => $this->subscription_status,
            'subscription_expires_at' => $this->subscription_expires_at?->toIso8601String(),
            'subscription_tier' => $tier,
            'subscription_tier_label' => PlatformClinicPresentation::tierLabel($tier),
            'plan_id' => $this->plan_id,
            'plan' => $this->whenLoaded('plan', fn () => [
                'id' => $this->plan?->id,
                'name' => $this->plan?->name,
            ]),
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
                'email' => $this->owner?->email,
            ]),
            'total_paid' => isset($this->total_paid) ? (float) $this->total_paid : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'stripe_status_label' => $this->when(
                $request->routeIs('api.v1.platform.clinics.show'),
                fn () => PlatformStripePresentation::statusLabel($this->resource)
            ),
        ];
    }
}
