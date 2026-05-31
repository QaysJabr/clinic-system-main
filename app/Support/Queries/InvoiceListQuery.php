<?php

namespace App\Support\Queries;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class InvoiceListQuery
{
    /**
     * نفس شروط فهرس الفواتير.
     */
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->integer('patient_id')))
            ->when($request->filled('invoice_number'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('invoice_number')).'%';
                $q->where('invoice_number', 'like', $term);
            })
            ->when($request->filled('patient'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('patient')).'%';
                $q->whereHas('patient', function ($inner) use ($term) {
                    $inner->where('full_name', 'like', $term);
                });
            })
            ->when(
                $request->filled('status') && in_array($request->input('status'), ['unpaid', 'partial', 'paid'], true),
                fn ($q) => $q->where('status', $request->input('status'))
            )
            ->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->integer('doctor_id')))
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date('date_from'));
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date('date_to'));
            })
            ->when($request->filled('date'), function ($q) use ($request) {
                $q->whereDate('created_at', $request->input('date'));
            });
    }

    public static function fromRequest(Request $request): Builder
    {
        $query = Invoice::query()
            ->select([
                'id',
                'invoice_number',
                'patient_id',
                'visit_id',
                'doctor_id',
                'total',
                'paid',
                'status',
                'due_date',
                'created_at',
            ])
            ->with([
                'patient:id,full_name',
                'treatingDoctor:id,full_name',
            ]);

        return self::apply($query, $request);
    }
}
