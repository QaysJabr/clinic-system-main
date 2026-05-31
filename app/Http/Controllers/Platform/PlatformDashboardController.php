<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformDashboardController extends Controller
{
    public function __construct(
        private readonly PlatformDashboardService $dashboard
    ) {}

    public function __invoke(Request $request): View
    {
        $pageTitle = __('platform.dashboard_title');
        $vars = array_merge($this->dashboard->metrics(), compact('pageTitle'));

        if ($request->ajax()) {
            return view('platform.partials.dashboard-inner', $vars);
        }

        return view('platform.dashboard', $vars);
    }
}
