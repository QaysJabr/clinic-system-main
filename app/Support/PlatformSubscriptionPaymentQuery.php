<?php

namespace App\Support;

use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PlatformSubscriptionPaymentQuery
{
    /**
     * @return Builder<SubscriptionPayment>
     */
    public static function succeededFromRequest(Request $request, ?int $clinicId = null): Builder
    {
        $query = SubscriptionPayment::query()
            ->where('status', SubscriptionPayment::STATUS_SUCCEEDED)
            ->with(['clinic:id,name']);

        if ($clinicId !== null) {
            $query->where('clinic_id', $clinicId);
        }

        if ($request->filled('paid_from')) {
            $query->whereDate('paid_at', '>=', $request->date('paid_from'));
        }

        if ($request->filled('paid_to')) {
            $query->whereDate('paid_at', '<=', $request->date('paid_to'));
        }

        return $query->orderByDesc('paid_at')->orderByDesc('id');
    }
}
