<?php

namespace App\Support\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class UserListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('search')).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $role = (string) $request->input('role');
                $q->whereHas('roles', fn ($r) => $r->where('name', $role)->where('guard_name', 'web'));
            });
    }
}
