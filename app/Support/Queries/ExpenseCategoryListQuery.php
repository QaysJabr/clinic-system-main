<?php

namespace App\Support\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ExpenseCategoryListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('status') && in_array($request->string('status')->toString(), ['active', 'inactive'], true), fn ($q) => $q->where('status', $request->string('status')));
    }
}
