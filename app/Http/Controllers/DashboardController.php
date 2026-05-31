<?php

namespace App\Http\Controllers;

use App\Services\ClinicDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ClinicDashboardService $dashboard,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        if ($user->hasRole('receptionist') && ! $request->ajax()) {
            return redirect()->route('reception.dashboard');
        }

        $viewData = $this->dashboard->buildForUser($user);
        $viewData['showOnboardingChecklist'] = (bool) session('show_onboarding', false);

        if ($request->ajax()) {
            return view('dashboard.partials.content', $viewData);
        }

        return view('dashboard', $viewData);
    }

    public function dismissOnboarding(Request $request): RedirectResponse
    {
        session()->forget('show_onboarding');

        return back();
    }
}
