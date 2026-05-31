<?php

namespace App\Support\Queries;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class StaffListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('search')).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('full_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('user', function ($userQ) use ($term) {
                            $userQ->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when(
                $request->filled('status') && in_array($request->input('status'), ['active', 'inactive'], true),
                fn ($q) => $q->where('status', $request->input('status'))
            )
            ->when(
                $request->filled('role_type') && in_array($request->input('role_type'), Staff::ROLE_TYPES, true),
                fn ($q) => $q->where('role_type', $request->input('role_type'))
            );
    }
}
