<?php

namespace App\Support\Queries;

use App\Models\DoctorEarning;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class DoctorEarningListQuery
{
    public static function apply(Builder $query, Request $request, bool $applyStatusFilter = true): Builder
    {
        return $query
            ->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->integer('doctor_id')))
            ->when($applyStatusFilter && $request->filled('status'), function ($q) use ($request): void {
                $status = (string) $request->string('status');
                if (in_array($status, [DoctorEarning::STATUS_PENDING, DoctorEarning::STATUS_PAID], true)) {
                    $q->where('status', $status);
                }
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')->format('Y-m-d')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')->format('Y-m-d')));
    }

    public static function applyDoctorScope(Builder $query, ?User $user): Builder
    {
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $query->where('doctor_id', $doc->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}
