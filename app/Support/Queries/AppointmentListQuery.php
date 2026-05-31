<?php

namespace App\Support\Queries;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class AppointmentListQuery
{
    /**
     * نفس شروط فهرس المواعيد.
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
                $request->filled('status') && in_array($request->input('status'), AppointmentStatus::values(), true),
                fn ($q) => $q->where('status', $request->input('status'))
            )
            ->when($request->filled('date'), function ($q) use ($request) {
                $q->whereDate('appointment_date', $request->input('date'));
            });
    }

    public static function fromRequest(Request $request): Builder
    {
        $query = Appointment::query()
            ->with(['patient:id,full_name', 'doctor:id,full_name']);

        return self::apply($query, $request);
    }
}
