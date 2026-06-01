<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ClinicSubscription;
use App\Models\Plan;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class SubscriptionService
{
    public function isActive(Clinic $clinic): bool
    {
        if ($clinic->is_active === false) {
            return false;
        }

        $sub = ClinicSubscription::query()
            ->where('clinic_id', $clinic->id)
            ->orderByDesc('id')
            ->first();

        if ($sub && $sub->isUsable()) {
            return true;
        }

        if ($clinic->subscription_status === Clinic::STATUS_EXPIRED) {
            return false;
        }

        if ($clinic->subscription_status !== Clinic::STATUS_ACTIVE) {
            return false;
        }

        if ($clinic->subscription_expires_at === null || $clinic->subscription_expires_at->isPast()) {
            return false;
        }

        return $clinic->subscription_expires_at->isFuture();
    }

    /**
     * اشتراك يدوي (بدون Stripe) أو تحديث الخطة — يُلغي الاشتراك النشط السابق.
     */
    public function subscribe(Clinic $clinic, Plan $plan, string $billingCycle): ClinicSubscription
    {
        if (! in_array($billingCycle, [Plan::CYCLE_MONTHLY, Plan::CYCLE_YEARLY], true)) {
            throw new \InvalidArgumentException('Invalid billing cycle.');
        }

        return DB::transaction(function () use ($clinic, $plan, $billingCycle): ClinicSubscription {
            $clinic = Clinic::query()->whereKey($clinic->id)->lockForUpdate()->firstOrFail();

            $amount = $plan->amountForBillingCycle($billingCycle);
            $startsAt = now();

            $trialDays = (int) ($plan->trial_days ?? 0);
            $status = $trialDays > 0 ? ClinicSubscription::STATUS_TRIAL : ClinicSubscription::STATUS_ACTIVE;

            $endsAt = $this->computeEndDate($startsAt, $billingCycle, $trialDays, $status === ClinicSubscription::STATUS_TRIAL);

            ClinicSubscription::query()
                ->where('clinic_id', $clinic->id)
                ->whereIn('status', [ClinicSubscription::STATUS_ACTIVE, ClinicSubscription::STATUS_TRIAL])
                ->update([
                    'status' => ClinicSubscription::STATUS_CANCELED,
                    'ends_at' => now(),
                ]);

            $subscription = ClinicSubscription::query()->create([
                'clinic_id' => $clinic->id,
                'plan_id' => $plan->id,
                'status' => $status,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            $clinic->forceFill([
                'plan_id' => $plan->id,
                'subscription_plan' => $plan->slug,
                'subscription_status' => Clinic::STATUS_ACTIVE,
                'subscription_expires_at' => $endsAt,
            ])->save();

            return $subscription;
        });
    }

    public function updatePlanOnRecord(Clinic $clinic, Plan $plan, string $billingCycle): void
    {
        $latest = $clinic->clinicSubscriptions()
            ->whereIn('status', [ClinicSubscription::STATUS_ACTIVE, ClinicSubscription::STATUS_TRIAL])
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            return;
        }

        $latest->update([
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'amount' => $plan->amountForBillingCycle($billingCycle),
        ]);

        $clinic->forceFill([
            'plan_id' => $plan->id,
            'subscription_plan' => $plan->slug,
        ])->save();
    }

    public function cancel(ClinicSubscription $subscription): void
    {
        $subscription->update([
            'status' => ClinicSubscription::STATUS_CANCELED,
            'ends_at' => now(),
        ]);

        $clinic = $subscription->clinic;
        if ($clinic) {
            $clinic->forceFill([
                'subscription_status' => Clinic::STATUS_EXPIRED,
            ])->save();
        }
    }

    public function activate(ClinicSubscription $subscription): void
    {
        $subscription->update([
            'status' => ClinicSubscription::STATUS_ACTIVE,
        ]);

        $clinic = $subscription->clinic;
        if ($clinic && $subscription->ends_at) {
            $clinic->forceFill([
                'subscription_status' => Clinic::STATUS_ACTIVE,
                'subscription_expires_at' => $subscription->ends_at,
            ])->save();
        }
    }

    /**
     * يعلّم سجلات الاشتراك المنتهية كـ «منتهية» (بدون تجديد تلقائي بدفع).
     */
    public function renewIfExpired(): int
    {
        return ClinicSubscription::query()
            ->whereIn('status', [ClinicSubscription::STATUS_ACTIVE, ClinicSubscription::STATUS_TRIAL])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->update(['status' => ClinicSubscription::STATUS_EXPIRED]);
    }

    private function computeEndDate(CarbonInterface $from, string $billingCycle, int $trialDays, bool $isTrial): ?Carbon
    {
        if ($isTrial && $trialDays > 0) {
            return $from->copy()->addDays($trialDays);
        }

        return $billingCycle === Plan::CYCLE_YEARLY
            ? $from->copy()->addYear()
            : $from->copy()->addMonth();
    }
}
