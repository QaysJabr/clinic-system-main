<?php

namespace App\Support\Queries;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ExpenseListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('expense_category_id'), fn ($q) => $q->where('expense_category_id', $request->integer('expense_category_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->date('date_to')))
            ->when($request->filled('payment_method'), function ($q) use ($request) {
                $pm = (string) $request->string('payment_method');
                $q->where(function ($inner) use ($pm) {
                    $inner->where('payment_method', $pm)
                        ->orWhereHas('payments', fn ($p) => $p->where('payment_method', $pm));
                });
            })
            ->when($request->filled('settlement_type') && in_array($request->input('settlement_type'), [Expense::SETTLEMENT_FULL, Expense::SETTLEMENT_INSTALLMENTS], true), function ($q) use ($request) {
                $q->where('settlement_type', $request->input('settlement_type'));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'ilike', $term)
                        ->orWhere('notes', 'ilike', $term);
                });
            })
            ->when($request->boolean('unpaid_only'), function ($q) {
                $q->where('settlement_type', Expense::SETTLEMENT_INSTALLMENTS)
                    ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM expense_payments WHERE expense_payments.expense_id = expenses.id) < expenses.amount - 0.009');
            });
    }
}
