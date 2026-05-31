<?php

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use App\Services\Inventory\InventoryDashboardService;
use App\Support\ClinicSettings;
use App\Support\Inventory\InventoryMovementType;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryReportController extends InventoryController
{
    public function __construct(
        private readonly InventoryDashboardService $dashboard,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $clinic = ClinicSettings::current();
        $stats = $this->dashboard->build();

        $lowStock = InventoryItem::query()
            ->with(['category', 'unit'])
            ->where('status', 'active')
            ->where('minimum_quantity', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'minimum_quantity')
            ->orderBy('quantity_on_hand')
            ->get();

        $expiring = InventoryItem::query()
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(60))
            ->orderBy('expiry_date')
            ->get();

        $movements = InventoryStockMovement::query()
            ->with('item:id,name')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest('movement_at')
            ->limit(50)
            ->get();

        $viewData = [
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'stats' => $stats,
            'lowStock' => $lowStock,
            'expiring' => $expiring,
            'movements' => $movements,
            'typeLabels' => InventoryMovementType::labels(),
            'pageTitle' => __('inventory.reports_title'),
        ];

        if ($request->ajax()) {
            return view('inventory.reports.partials.content', $viewData);
        }

        return view('inventory.reports.index', $viewData);
    }
}
