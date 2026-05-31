<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\Visit;
use App\Services\InAppNotificationService;

final class InventoryAlertService
{
    public function __construct(
        private readonly InAppNotificationService $notifications,
    ) {}

    public function notifyLowStock(InventoryItem $item): void
    {
        $this->notifications->notifyInventoryLowStock($item);
    }

    public function notifyExpiring(InventoryItem $item): void
    {
        $this->notifications->notifyInventoryExpiring($item);
    }

    public function notifyConsumptionFailed(Visit $visit, string $procedureName, string $reason): void
    {
        $this->notifications->notifyInventoryConsumptionFailed($visit, $procedureName, $reason);
    }

    /**
     * Scan clinic items and emit deduped alerts (dashboard / scheduled use).
     */
    public function scanClinic(int $clinicId): void
    {
        InventoryItem::query()
            ->where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->whereColumn('quantity_on_hand', '<=', 'minimum_quantity')
            ->where('minimum_quantity', '>', 0)
            ->each(fn (InventoryItem $item) => $this->notifyLowStock($item));

        InventoryItem::query()
            ->where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->each(fn (InventoryItem $item) => $this->notifyExpiring($item));
    }
}
