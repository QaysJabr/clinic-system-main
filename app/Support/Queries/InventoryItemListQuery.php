<?php

namespace App\Support\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class InventoryItemListQuery
{
    public static function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('barcode', 'like', $term);
                });
            })
            ->when($request->filled('inventory_category_id'), fn ($q) => $q->where('inventory_category_id', $request->integer('inventory_category_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->boolean('low_stock'), function ($q): void {
                $q->where('minimum_quantity', '>', 0)
                    ->whereColumn('quantity_on_hand', '<=', 'minimum_quantity');
            });
    }
}
