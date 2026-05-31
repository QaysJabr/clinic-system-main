<?php

namespace App\Support;

use App\Models\InventoryPurchase;
use App\Services\Inventory\InventoryDashboardService;

/**
 * Inventory metrics for the main clinic reports page (tenant-scoped).
 */
final class InventoryReportStats
{
    /**
     * @return array{
     *   inventoryValuation: float,
     *   inventoryLowStockCount: int,
     *   inventoryExpiringCount: int,
     *   inventoryTotalSkus: int,
     *   inventoryPurchasesThisMonth: float,
     *   inventoryPurchasesTotal: float,
     * }
     */
    public static function snapshot(): array
    {
        $dash = app(InventoryDashboardService::class)->build();

        $monthStart = now()->startOfMonth()->toDateString();

        $purchasesThisMonth = (float) InventoryPurchase::query()
            ->where('status', InventoryPurchase::STATUS_RECEIVED)
            ->whereDate('purchase_date', '>=', $monthStart)
            ->sum('total_amount');

        $purchasesTotal = (float) InventoryPurchase::query()
            ->where('status', InventoryPurchase::STATUS_RECEIVED)
            ->sum('total_amount');

        return [
            'inventoryValuation' => (float) ($dash['valuation'] ?? 0),
            'inventoryLowStockCount' => (int) ($dash['low_stock_count'] ?? 0),
            'inventoryExpiringCount' => (int) ($dash['expiring_soon_count'] ?? 0),
            'inventoryTotalSkus' => (int) ($dash['total_skus'] ?? 0),
            'inventoryPurchasesThisMonth' => round($purchasesThisMonth, 2),
            'inventoryPurchasesTotal' => round($purchasesTotal, 2),
        ];
    }
}
