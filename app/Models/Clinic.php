<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;

class Clinic extends Model
{
    use Billable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'name',
        'owner_id',
        'subscription_plan',
        'subscription_status',
        'subscription_expires_at',
        'plan_id',
        'is_active',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'subscription_expires_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Stripe customer email comes from the clinic owner account.
     */
    public function stripeEmail(): ?string
    {
        return $this->owner?->email;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * آخر سجل اشتراك SaaS (للتحقق من الحالة عبر {@see SubscriptionService}).
     *
     * @return HasOne<ClinicSubscription, $this>
     */
    public function latestClinicSubscription(): HasOne
    {
        return $this->hasOne(ClinicSubscription::class)->latestOfMany();
    }

    /**
     * @return HasMany<ClinicSubscription, $this>
     */
    public function clinicSubscriptions(): HasMany
    {
        return $this->hasMany(ClinicSubscription::class);
    }

    public function canAddPatient(): bool
    {
        $plan = $this->relationLoaded('plan') ? $this->plan : $this->plan()->first();
        if (! $plan || $plan->max_patients === null) {
            return true;
        }

        $count = Patient::query()->where('clinic_id', $this->id)->count();

        return $count < $plan->max_patients;
    }

    public function canAddUser(): bool
    {
        $plan = $this->relationLoaded('plan') ? $this->plan : $this->plan()->first();
        if (! $plan || $plan->max_users === null) {
            return true;
        }

        $count = User::query()->where('clinic_id', $this->id)->count();

        return $count < $plan->max_users;
    }

    /**
     * SaaS subscription charges (not patient invoice payments).
     *
     * @return HasMany<SubscriptionPayment, $this>
     */
    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function subscriptionIsActive(): bool
    {
        return app(\App\Services\SubscriptionService::class)->isActive($this);
    }
}
