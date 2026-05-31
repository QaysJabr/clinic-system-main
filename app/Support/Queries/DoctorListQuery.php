<?php

namespace App\Support\Queries;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class DoctorListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('search')).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('full_name', 'like', $term)
                        ->orWhere('specialty', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('license_number', 'like', $term)
                        ->orWhere('room_number', 'like', $term);
                });
            })
            ->when(
                $request->filled('status') && in_array($request->input('status'), ['active', 'inactive'], true),
                fn ($q) => $q->where('status', $request->input('status'))
            );
    }

    public static function fromRequest(Request $request): Builder
    {
        return self::apply(Doctor::query(), $request);
    }
}
