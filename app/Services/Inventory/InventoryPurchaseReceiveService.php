<?php

namespace App\Services\Inventory;

use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseLine;
use App\Support\Inventory\InventoryMovementType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class InventoryPurchaseReceiveService
{
    public function __construct(
        private readonly InventoryStockMovementService $stock,
    ) {}

    /**
     * @param  list<array{
     *   inventory_item_id: int,
     *   quantity: float,
     *   unit_cost?: float|null,
     *   expiry_date?: string|null,
     *   batch_number?: string|null,
     * }>  $lines
     */
    public function receive(
        ?int $supplierId,
        string $purchaseDate,
        array $lines,
        ?string $referenceNumber = null,
        ?string $notes = null,
    ): InventoryPurchase {
        return DB::transaction(function () use ($supplierId, $purchaseDate, $lines, $referenceNumber, $notes): InventoryPurchase {
            $total = 0.0;

            $purchase = InventoryPurchase::query()->create([
                'inventory_supplier_id' => $supplierId,
                'reference_number' => $referenceNumber,
                'purchase_date' => $purchaseDate,
                'status' => InventoryPurchase::STATUS_RECEIVED,
                'notes' => $notes,
                'created_by' => Auth::id(),
                'received_at' => now(),
            ]);

            foreach ($lines as $row) {
                $qty = (float) ($row['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $unitCost = isset($row['unit_cost']) ? (float) $row['unit_cost'] : null;
                $total += $qty * ($unitCost ?? 0);

                $line = InventoryPurchaseLine::query()->create([
                    'inventory_purchase_id' => $purchase->id,
                    'inventory_item_id' => (int) $row['inventory_item_id'],
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'batch_number' => $row['batch_number'] ?? null,
                ]);

                $item = $line->item()->firstOrFail();

                if (filled($row['expiry_date'] ?? null)) {
                    $item->expiry_date = $row['expiry_date'];
                }
                if (filled($row['batch_number'] ?? null)) {
                    $item->batch_number = $row['batch_number'];
                }
                if ($unitCost !== null) {
                    $item->unit_cost = $unitCost;
                }
                $item->save();

                $this->stock->record(
                    $item,
                    InventoryMovementType::STOCK_IN,
                    $qty,
                    [
                        'inventory_purchase_id' => $purchase->id,
                        'notes' => __('inventory.purchase_stock_in_note', ['ref' => $purchase->documentNumber()]),
                    ],
                );
            }

            $purchase->update(['total_amount' => round($total, 2)]);

            return $purchase->load(['lines.item', 'supplier']);
        });
    }
}
