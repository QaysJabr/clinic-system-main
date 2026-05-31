<?php

namespace App\Support\Queries;

use App\Enums\PaymentCycle;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class StaffPaymentListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('staff_id'), fn ($q) => $q->where('staff_id', $request->integer('staff_id')))
            ->when($request->filled('role_type'), function ($q) use ($request): void {
                $roleType = (string) $request->string('role_type');
                if (in_array($roleType, Staff::ROLE_TYPES, true)) {
                    $q->whereHas('staff', fn ($staff) => $staff->where('role_type', $roleType));
                }
            })
            ->when($request->filled('filter_period_type'), function ($q) use ($request): void {
                $pt = (string) $request->string('filter_period_type');
                if (in_array($pt, PaymentCycle::values(), true)) {
                    $q->where('period_type', $pt);
                }
            })
            ->when($request->filled('filter_period_from'), fn ($q) => $q->whereDate('period_start', '>=', $request->date('filter_period_from')->format('Y-m-d')))
            ->when($request->filled('filter_period_to'), fn ($q) => $q->whereDate('period_end', '<=', $request->date('filter_period_to')->format('Y-m-d')))
            ->when($request->filled('payment_status'), function ($q) use ($request): void {
                match ((string) $request->string('payment_status')) {
                    'completed' => $q->where('remaining_amount', '<=', 0.01),
                    'unpaid' => $q->where('paid_amount', '<=', 0.01)->where('total_due', '>', 0.01),
                    'partial' => $q->where('paid_amount', '>', 0.01)->where('remaining_amount', '>', 0.01),
                    default => null,
                };
            });
    }
}
