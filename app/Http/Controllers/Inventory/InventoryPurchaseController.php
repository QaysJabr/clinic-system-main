<?php

namespace App\Http\Controllers\Inventory;

use App\Models\ExpenseCategory;
use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\InventorySupplier;
use App\Services\Inventory\InventoryPurchaseExpenseService;
use App\Services\Inventory\InventoryPurchaseReceiveService;
use App\Support\AuditLogger;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryPurchaseController extends InventoryController
{
    public function __construct(
        private readonly InventoryPurchaseReceiveService $receive,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $purchaseBase = InventoryPurchase::query();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $purchaseStats = [
            'month_count' => (clone $purchaseBase)->whereBetween('purchase_date', [$monthStart, $monthEnd])->count(),
            'month_total' => (float) (clone $purchaseBase)->whereBetween('purchase_date', [$monthStart, $monthEnd])->sum('total_amount'),
            'with_expense' => (clone $purchaseBase)->whereNotNull('expense_id')->count(),
            'all' => (clone $purchaseBase)->count(),
        ];

        $purchases = InventoryPurchase::query()
            ->with(['supplier', 'creator:id,name', 'expense:id,title,amount'])
            ->latest('purchase_date')
            ->paginate(20);

        $viewData = [
            'purchases' => $purchases,
            'purchaseStats' => $purchaseStats,
            'pageTitle' => __('inventory.purchases_title'),
        ];

        if ($request->ajax()) {
            return view('inventory.purchases.partials.content', $viewData);
        }

        return view('inventory.purchases.index', $viewData);
    }

    public function create(): View
    {
        $this->authorize('create', InventoryItem::class);

        return view('inventory.purchases.create', [
            'suppliers' => InventorySupplier::query()->where('is_active', true)->orderBy('name')->get(),
            'items' => InventoryItem::query()->where('status', 'active')->orderBy('name')->get(),
            'expenseCategories' => ExpenseCategory::query()->where('status', 'active')->orderBy('name')->get(),
            'pageTitle' => __('inventory.purchase_create_title'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $clinicId = TenantValidation::clinicIdForRules();

        $data = $request->validate([
            'inventory_supplier_id' => ['nullable', 'integer', 'exists:inventory_suppliers,id,clinic_id,'.$clinicId],
            'reference_number' => ['nullable', 'string', 'max:64'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id,clinic_id,'.$clinicId],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'lines.*.expiry_date' => ['nullable', 'date'],
            'lines.*.batch_number' => ['nullable', 'string', 'max:64'],
            'post_to_expense' => ['sometimes', 'boolean'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id,clinic_id,'.$clinicId],
        ]);

        $purchase = $this->receive->receive(
            $data['inventory_supplier_id'] ?? null,
            $data['purchase_date'],
            $data['lines'],
            $data['reference_number'] ?? null,
            $data['notes'] ?? null,
        );

        if ($request->boolean('post_to_expense')) {
            app(InventoryPurchaseExpenseService::class)->createFromPurchase(
                $purchase->fresh(['supplier']),
                $data['expense_category_id'] ?? null,
            );
        }

        AuditLogger::log('create', 'inventory_purchases', $purchase->id, __('inventory.audit_purchase_received', ['ref' => $purchase->documentNumber()]));

        return redirect()->route('inventory.purchases.index')->with('success', __('inventory.flash_purchase_received'));
    }
}
