<?php

namespace App\Support\Queries;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class AuditLogListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('module') && $request->input('module') !== '', fn ($q) => $q->where('module', $request->input('module')))
            ->when($request->filled('action') && $request->input('action') !== '', fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->input('date')));
    }

    public static function fromRequest(Request $request): Builder
    {
        return self::apply(AuditLog::query(), $request);
    }
}
