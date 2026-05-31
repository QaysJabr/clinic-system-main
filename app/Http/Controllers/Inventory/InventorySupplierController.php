<?php

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryItem;
use App\Models\InventorySupplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventorySupplierController extends InventoryController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $supplierBase = InventorySupplier::query();
        $supplierStats = [
            'total' => (clone $supplierBase)->count(),
            'active' => (clone $supplierBase)->where('is_active', true)->count(),
        ];

        $suppliers = InventorySupplier::query()->orderBy('name')->paginate(20);

        $viewData = [
            'suppliers' => $suppliers,
            'supplierStats' => $supplierStats,
            'pageTitle' => __('inventory.suppliers_title'),
        ];

        if ($request->ajax()) {
            return view('inventory.suppliers.partials.content', $viewData);
        }

        return view('inventory.suppliers.index', $viewData);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        InventorySupplier::query()->create($data + ['is_active' => true]);

        return redirect()->route('inventory.suppliers.index')->with('success', __('inventory.flash_supplier_created'));
    }
}
