<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use App\Support\Inventory\InventoryMovementType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single entry point for quantity changes and movement audit trail.
 */
final class InventoryStockMovementService
{
    /**
     * @param  array{
     *   visit_id?: int|null,
     *   inventory_purchase_id?: int|null,
     *   reference_type?: string|null,
     *   reference_id?: int|null,
     *   notes?: string|null,
     *   movement_at?: \DateTimeInterface|string|null,
     * }  $context
     */
    public function record(
        InventoryItem $item,
        string $type,
        float $quantity,
        array $context = [],
    ): InventoryStockMovement {
        if ($quantity <= 0) {
            throw new RuntimeException(__('inventory.error_quantity_positive'));
        }

        if (! in_array($type, InventoryMovementType::all(), true)) {
            throw new RuntimeException(__('inventory.error_invalid_movement_type'));
        }

        return DB::transaction(function () use ($item, $type, $quantity, $context): InventoryStockMovement {
            $locked = InventoryItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $before = (float) $locked->quantity_on_hand;
            $delta = $this->resolveDelta($type, $quantity);
            $after = round($before + $delta, 3);

            if ($after < 0 && $type !== InventoryMovementType::ADJUSTMENT) {
                throw new RuntimeException(__('inventory.error_insufficient_stock', [
                    'item' => $locked->name,
                    'available' => number_format($before, 3),
                ]));
            }

            $locked->quantity_on_hand = max(0, $after);
            $locked->save();

            return InventoryStockMovement::query()->create([
                'clinic_id' => $locked->clinic_id,
                'inventory_item_id' => $locked->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $locked->quantity_on_hand,
                'visit_id' => $context['visit_id'] ?? null,
                'inventory_purchase_id' => $context['inventory_purchase_id'] ?? null,
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'notes' => $context['notes'] ?? null,
                'created_by' => Auth::id(),
                'movement_at' => $context['movement_at'] ?? now(),
            ]);
        });
    }

    /**
     * Set absolute on-hand quantity (manual correction).
     */
    public function setQuantity(InventoryItem $item, float $targetQuantity, ?string $notes = null): InventoryStockMovement
    {
        $targetQuantity = max(0, round($targetQuantity, 3));
        $current = (float) $item->quantity_on_hand;
        $diff = abs($targetQuantity - $current);

        if ($diff < 0.0005) {
            throw new RuntimeException(__('inventory.error_no_quantity_change'));
        }

        $type = InventoryMovementType::CORRECTION;

        return DB::transaction(function () use ($item, $targetQuantity, $current, $diff, $notes, $type): InventoryStockMovement {
            $locked = InventoryItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $locked->quantity_on_hand = $targetQuantity;
            $locked->save();

            return InventoryStockMovement::query()->create([
                'clinic_id' => $locked->clinic_id,
                'inventory_item_id' => $locked->id,
                'type' => $type,
                'quantity' => $diff,
                'quantity_before' => $current,
                'quantity_after' => $targetQuantity,
                'notes' => $notes,
                'created_by' => Auth::id(),
                'movement_at' => now(),
            ]);
        });
    }

    private function resolveDelta(string $type, float $quantity): float
    {
        if ($type === InventoryMovementType::ADJUSTMENT) {
            return $quantity;
        }

        if (InventoryMovementType::increasesStock($type) || $type === InventoryMovementType::STOCK_IN) {
            return $quantity;
        }

        return -$quantity;
    }
}
