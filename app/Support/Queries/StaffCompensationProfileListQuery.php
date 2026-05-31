<?php

namespace App\Support\Queries;

use App\Enums\StaffCompensationModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class StaffCompensationProfileListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $q->whereHas('staff', function ($staff) use ($term): void {
                    $staff->where('full_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($request->filled('status') && in_array($request->string('status')->toString(), ['active', 'inactive'], true), fn ($q) => $q->where('status', $request->string('status')))
            ->when(
                $request->filled('compensation_type') && in_array($request->string('compensation_type')->toString(), StaffCompensationModel::values(), true),
                fn ($q) => $q->where('compensation_type', $request->string('compensation_type')),
            );
    }
}
