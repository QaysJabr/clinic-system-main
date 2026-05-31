<?php

namespace App\Support\Queries;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class VisitListQuery
{
    /** @var list<string> */
    private const STATUSES = [
        Visit::STATUS_WAITING,
        Visit::STATUS_IN_PROGRESS,
        Visit::STATUS_COMPLETED,
        Visit::STATUS_CANCELLED,
    ];

    /**
     * نفس شروط فهرس الزيارات.
     */
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('patient'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('patient')).'%';
                $q->whereHas('patient', function ($inner) use ($term) {
                    $inner->where('full_name', 'like', $term);
                });
            })
            ->when($request->filled('doctor'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('doctor')).'%';
                $q->whereHas('doctor', function ($inner) use ($term) {
                    $inner->where('full_name', 'like', $term);
                });
            })
            ->when(
                $request->filled('status') && in_array($request->input('status'), self::STATUSES, true),
                fn ($q) => $q->where('status', $request->input('status'))
            )
            ->when($request->filled('date'), function ($q) use ($request) {
                $q->whereDate('visit_date', $request->input('date'));
            });
    }

    public static function fromRequest(Request $request): Builder
    {
        $query = Visit::query()
            ->with([
                'patient:id,full_name',
                'doctor:id,full_name',
                'appointment:id,appointment_date',
            ]);

        return self::apply($query, $request);
    }
}
