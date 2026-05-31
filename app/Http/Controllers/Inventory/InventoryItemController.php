<?php

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventorySupplier;
use App\Models\InventoryUnit;
use App\Support\AuditLogger;
use App\Support\ClinicSettings;
use App\Support\Inventory\InventoryItemStatus;
use App\Support\Queries\InventoryItemListQuery;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryItemController extends InventoryController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $itemsBase = InventoryItem::query();
        $expiringEnd = now()->addDays(30)->endOfDay();

        $itemStats = [
            'total' => (clone $itemsBase)->where('status', InventoryItemStatus::ACTIVE)->count(),
            'low_stock' => (clone $itemsBase)
                ->where('status', InventoryItemStatus::ACTIVE)
                ->where('minimum_quantity', '>', 0)
                ->whereColumn('quantity_on_hand', '<=', 'minimum_quantity')
                ->count(),
            'expiring' => (clone $itemsBase)
                ->where('status', InventoryItemStatus::ACTIVE)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now()->startOfDay(), $expiringEnd])
                ->count(),
            'inactive' => (clone $itemsBase)->where('status', '!=', InventoryItemStatus::ACTIVE)->count(),
        ];

        $query = InventoryItemListQuery::apply(
            InventoryItem::query()
                ->with(['category', 'unit', 'supplier'])
                ->orderBy('name'),
            $request,
        );

        $items = $query->paginate(20)->withQueryString();

        $viewData = [
            'items' => $items,
            'itemStats' => $itemStats,
            'categories' => InventoryCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'pageTitle' => __('inventory.items_title'),
            'statusLabels' => InventoryItemStatus::labels(),
        ];

        if ($request->ajax()) {
            return view('inventory.items.partials.content', $viewData);
        }

        return view('inventory.items.index', $viewData);
    }

    public function create(): View
    {
        $this->authorize('create', InventoryItem::class);

        return view('inventory.items.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $this->validated($request);
        $item = InventoryItem::query()->create($data);

        AuditLogger::log('create', 'inventory', $item->id, __('inventory.audit_item_created', ['name' => $item->name]));

        return redirect()
            ->route('inventory.items.index')
            ->with('success', __('inventory.flash_item_created'));
    }

    public function edit(InventoryItem $item): View
    {
        $this->authorize('update', $item);

        return view('inventory.items.edit', array_merge($this->formData(), ['item' => $item]));
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $old = $item->only(['name', 'quantity_on_hand', 'minimum_quantity', 'status']);
        $item->update($this->validated($request, $item));

        AuditLogger::log('update', 'inventory', $item->id, __('inventory.audit_item_updated', ['name' => $item->name]), $old, $item->only(array_keys($old)));

        return redirect()
            ->route('inventory.items.index')
            ->with('success', __('inventory.flash_item_updated'));
    }

    public function destroy(InventoryItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        $name = $item->name;
        $id = $item->id;
        $item->delete();

        AuditLogger::log('delete', 'inventory', $id, __('inventory.audit_item_deleted', ['name' => $name]));

        return redirect()
            ->route('inventory.items.index')
            ->with('success', __('inventory.flash_item_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => InventoryCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'units' => InventoryUnit::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => InventorySupplier::query()->where('is_active', true)->orderBy('name')->get(),
            'statusLabels' => InventoryItemStatus::labels(),
            'clinic' => ClinicSettings::current(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?InventoryItem $item = null): array
    {
        $clinicId = TenantValidation::clinicIdForRules();

        $skuRule = 'nullable|string|max:64';
        if ($item) {
            $skuRule .= '|unique:inventory_items,sku,'.$item->id.',id,clinic_id,'.$clinicId;
        } else {
            $skuRule .= '|unique:inventory_items,sku,NULL,id,clinic_id,'.$clinicId;
        }

        return $request->validate([
            'inventory_category_id' => ['nullable', 'integer', 'exists:inventory_categories,id,clinic_id,'.$clinicId],
            'inventory_unit_id' => ['nullable', 'integer', 'exists:inventory_units,id,clinic_id,'.$clinicId],
            'inventory_supplier_id' => ['nullable', 'integer', 'exists:inventory_suppliers,id,clinic_id,'.$clinicId],
            'name' => ['required', 'string', 'max:255'],
            'sku' => $skuRule,
            'quantity_on_hand' => ['nullable', 'numeric', 'min:0'],
            'minimum_quantity' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:'.implode(',', InventoryItemStatus::all())],
            'expiry_date' => ['nullable', 'date'],
            'batch_number' => ['nullable', 'string', 'max:64'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
