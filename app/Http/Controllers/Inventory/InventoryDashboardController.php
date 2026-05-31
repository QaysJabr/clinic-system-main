<?php

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryItem;
use App\Services\Inventory\ClinicInventorySetupService;
use App\Services\Inventory\InventoryDashboardService;
use App\Support\ClinicSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryDashboardController extends InventoryController
{
    public function __construct(
        private readonly InventoryDashboardService $dashboard,
        private readonly ClinicInventorySetupService $setup,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $settings = ClinicSettings::current();
        $this->setup->ensureDefaults((int) $settings->clinic_id);

        $viewData = array_merge(
            $this->dashboard->build(),
            [
                'clinic' => $settings,
                'cur' => $settings->currency ?? '',
                'pageTitle' => __('inventory.dashboard_title'),
            ],
        );

        if ($request->ajax()) {
            return view('inventory.dashboard.partials.content', $viewData);
        }

        return view('inventory.dashboard.index', $viewData);
    }
}
