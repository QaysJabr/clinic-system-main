<?php

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use App\Services\Inventory\InventoryAlertService;
use App\Services\Inventory\InventoryStockMovementService;
use App\Support\AuditLogger;
use App\Support\Inventory\InventoryMovementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryStockMovementController extends InventoryController
{
    public function __construct(
        private readonly InventoryStockMovementService $stock,
        private readonly InventoryAlertService $alerts,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $movementBase = InventoryStockMovement::query();
        $inboundTypes = [InventoryMovementType::STOCK_IN, InventoryMovementType::CORRECTION];
        $outboundTypes = [
            InventoryMovementType::STOCK_OUT,
            InventoryMovementType::DAMAGED,
            InventoryMovementType::EXPIRED,
            InventoryMovementType::CONSUMPTION,
        ];

        $movementStats = [
            'today' => (clone $movementBase)->whereDate('movement_at', today())->count(),
            'inbound' => (clone $movementBase)->whereIn('type', $inboundTypes)->where('movement_at', '>=', now()->subDays(30))->count(),
            'outbound' => (clone $movementBase)->whereIn('type', $outboundTypes)->where('movement_at', '>=', now()->subDays(30))->count(),
            'consumption' => (clone $movementBase)->where('type', InventoryMovementType::CONSUMPTION)->where('movement_at', '>=', now()->subDays(30))->count(),
        ];

        $movements = InventoryStockMovement::query()
            ->with(['item:id,name,sku', 'creator:id,name', 'visit:id'])
            ->when($request->filled('inventory_item_id'), fn ($q) => $q->where('inventory_item_id', $request->integer('inventory_item_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest('movement_at')
            ->paginate(25)
            ->withQueryString();

        $viewData = [
            'movements' => $movements,
            'movementStats' => $movementStats,
            'typeLabels' => InventoryMovementType::labels(),
            'items' => InventoryItem::query()->orderBy('name')->get(['id', 'name']),
            'pageTitle' => __('inventory.movements_title'),
        ];

        if ($request->ajax()) {
            return view('inventory.movements.partials.content', $viewData);
        }

        return view('inventory.movements.index', $viewData);
    }

    public function create(): View
    {
        $this->authorize('create', InventoryItem::class);

        return view('inventory.movements.create', [
            'items' => InventoryItem::query()->where('status', 'active')->orderBy('name')->get(),
            'typeLabels' => InventoryMovementType::labels(),
            'pageTitle' => __('inventory.movement_create_title'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'type' => ['required', 'string', 'in:'.implode(',', InventoryMovementType::all())],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
            'target_quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = InventoryItem::query()->findOrFail($data['inventory_item_id']);

        if ($data['type'] === InventoryMovementType::CORRECTION && isset($data['target_quantity'])) {
            $this->stock->setQuantity($item, (float) $data['target_quantity'], $data['notes'] ?? null);
        } else {
            $this->stock->record($item, $data['type'], (float) $data['quantity'], [
                'notes' => $data['notes'] ?? null,
            ]);
        }

        $item->refresh();
        if ($item->isLowStock()) {
            $this->alerts->notifyLowStock($item);
        }

        AuditLogger::log('create', 'inventory_movements', $item->id, __('inventory.audit_movement_recorded', ['name' => $item->name]));

        return redirect()
            ->route('inventory.movements.index')
            ->with('success', __('inventory.flash_movement_recorded'));
    }
}
