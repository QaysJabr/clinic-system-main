<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    public const CYCLE_MONTHLY = 'monthly';

    public const CYCLE_YEARLY = 'yearly';

    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'price_yearly',
        'max_patients',
        'max_users',
        'features',
        'trial_days',
        'stripe_price_id',
        'stripe_price_yearly_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'max_patients' => 'integer',
            'max_users' => 'integer',
            'features' => 'array',
            'trial_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Clinic, $this>
     */
    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }

    /**
     * @return HasMany<ClinicSubscription, $this>
     */
    public function clinicSubscriptions(): HasMany
    {
        return $this->hasMany(ClinicSubscription::class, 'plan_id');
    }

    public function amountForBillingCycle(string $cycle): float
    {
        return (float) ($cycle === self::CYCLE_YEARLY
            ? $this->price_yearly
            : $this->price_monthly);
    }

    public function stripePriceIdForBillingCycle(string $cycle): ?string
    {
        if ($cycle === self::CYCLE_YEARLY) {
            return $this->stripe_price_yearly_id ?: $this->stripe_price_id;
        }

        return $this->stripe_price_id;
    }

    public function displayMonthly(): string
    {
        return $this->formatMoney((float) $this->price_monthly);
    }

    public function displayYearly(): string
    {
        return $this->formatMoney((float) $this->price_yearly);
    }

    /**
     * @deprecated Use displayMonthly / displayYearly
     */
    public function displayPrice(): string
    {
        if ((float) $this->price_yearly > 0 && (float) $this->price_monthly <= 0) {
            return $this->displayYearly().' / سنوي';
        }

        return $this->displayMonthly().' / شهري';
    }

    private function formatMoney(float $amount): string
    {
        $cur = strtoupper(config('cashier.currency', 'usd'));

        return number_format($amount, 2).' '.$cur;
    }
}
