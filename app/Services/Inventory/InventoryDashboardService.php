<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use App\Support\Inventory\InventoryItemStatus;
use App\Support\Inventory\InventoryMovementType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class InventoryDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $activeItems = InventoryItem::query()->where('status', InventoryItemStatus::ACTIVE);

        $totalSkus = (clone $activeItems)->count();
        $lowStock = (clone $activeItems)
            ->where('minimum_quantity', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'minimum_quantity')
            ->count();

        $expiringSoon = (clone $activeItems)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->count();

        $valuation = (float) (clone $activeItems)
            ->selectRaw('COALESCE(SUM(quantity_on_hand * COALESCE(unit_cost, 0)), 0) as total')
            ->value('total');

        return [
            'total_skus' => $totalSkus,
            'low_stock_count' => $lowStock,
            'expiring_soon_count' => $expiringSoon,
            'valuation' => round($valuation, 2),
            'low_stock_items' => $this->lowStockItems(),
            'expiring_items' => $this->expiringItems(),
            'recent_movements' => $this->recentMovements(),
            'top_consumed' => $this->topConsumed(30),
        ];
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    private function lowStockItems(): Collection
    {
        return InventoryItem::query()
            ->with(['category', 'unit'])
            ->where('status', InventoryItemStatus::ACTIVE)
            ->where('minimum_quantity', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'minimum_quantity')
            ->orderBy('quantity_on_hand')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    private function expiringItems(): Collection
    {
        return InventoryItem::query()
            ->with(['category', 'unit'])
            ->where('status', InventoryItemStatus::ACTIVE)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30)->endOfDay())
            ->orderBy('expiry_date')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, InventoryStockMovement>
     */
    private function recentMovements(): Collection
    {
        return InventoryStockMovement::query()
            ->with(['item:id,name,sku', 'creator:id,name'])
            ->latest('movement_at')
            ->limit(12)
            ->get();
    }

    /**
     * @return list<array{item_name: string, total_qty: float}>
     */
    private function topConsumed(int $days): array
    {
        $since = Carbon::now()->subDays($days);

        return InventoryStockMovement::query()
            ->select('inventory_item_id', DB::raw('SUM(quantity) as total_qty'))
            ->where('type', InventoryMovementType::CONSUMPTION)
            ->where('movement_at', '>=', $since)
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_qty')
            ->limit(6)
            ->get()
            ->map(function ($row): array {
                $item = InventoryItem::query()->find($row->inventory_item_id);

                return [
                    'item_name' => $item?->name ?? '—',
                    'total_qty' => (float) $row->total_qty,
                ];
            })
            ->all();
    }
}
