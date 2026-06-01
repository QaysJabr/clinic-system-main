<?php

namespace App\Services\Inventory;

use App\Models\InventoryProcedureTemplate;
use App\Models\InventoryStockMovement;
use App\Models\Visit;
use App\Models\VisitProcedure;
use App\Support\Inventory\InventoryItemStatus;
use App\Support\Inventory\InventoryMovementType;
use App\Support\Inventory\InventoryNameNormalizer;
use Illuminate\Support\Facades\DB;

final class InventoryProcedureConsumptionService
{
    public function __construct(
        private readonly InventoryStockMovementService $stock,
        private readonly InventoryAlertService $alerts,
    ) {}

    /**
     * Consume materials when a visit is marked completed (once per visit).
     *
     * @return list<InventoryStockMovement>
     */
    public function consumeForCompletedVisit(Visit $visit): array
    {
        if ($visit->status !== Visit::STATUS_COMPLETED) {
            return [];
        }

        $movements = [];

        DB::transaction(function () use ($visit, &$movements): void {
            $locked = Visit::withoutGlobalScopes()
                ->whereKey($visit->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || $locked->status !== Visit::STATUS_COMPLETED) {
                return;
            }

            if ($this->visitAlreadyConsumed($locked)) {
                return;
            }

            $locked->loadMissing('structuredProcedures');

            foreach ($locked->structuredProcedures as $procedure) {
                $template = $this->findTemplate((int) $locked->clinic_id, $procedure->name);
                if ($template === null || ! $template->auto_consume) {
                    continue;
                }

                $template->loadMissing(['lines.item']);

                foreach ($template->lines as $line) {
                    $item = $line->item;
                    if ($item === null || $item->status !== InventoryItemStatus::ACTIVE) {
                        continue;
                    }

                    try {
                        $movement = $this->stock->record(
                            $item,
                            InventoryMovementType::CONSUMPTION,
                            (float) $line->quantity,
                            [
                                'visit_id' => $locked->id,
                                'reference_type' => VisitProcedure::class,
                                'reference_id' => $procedure->id,
                                'notes' => __('inventory.consumption_note', [
                                    'procedure' => $procedure->name,
                                    'visit' => $locked->id,
                                ]),
                            ],
                        );
                        $movements[] = $movement;
                    } catch (\Throwable $e) {
                        $this->alerts->notifyConsumptionFailed($locked, $procedure->name, $e->getMessage());
                    }
                }
            }
        });

        foreach ($movements as $movement) {
            $movement->loadMissing('item');
            if ($movement->item?->isLowStock()) {
                $this->alerts->notifyLowStock($movement->item);
            }
        }

        return $movements;
    }

    private function visitAlreadyConsumed(Visit $visit): bool
    {
        return InventoryStockMovement::query()
            ->where('visit_id', $visit->id)
            ->where('type', InventoryMovementType::CONSUMPTION)
            ->exists();
    }

    private function findTemplate(int $clinicId, string $procedureName): ?InventoryProcedureTemplate
    {
        $normalized = InventoryNameNormalizer::normalize($procedureName);

        return InventoryProcedureTemplate::query()
            ->where('clinic_id', $clinicId)
            ->where('name_normalized', $normalized)
            ->where('is_active', true)
            ->with('lines')
            ->first();
    }
}
