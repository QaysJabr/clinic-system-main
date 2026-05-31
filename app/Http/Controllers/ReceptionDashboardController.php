<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\ClinicSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * لوحة استقبال: قائمة انتظار، روابط العمل اليومية.
 */
final class ReceptionDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = now()->toDateString();

        $waitingQueue = Visit::query()
            ->whereDate('visit_date', $today)
            ->whereIn('status', [Visit::STATUS_WAITING, Visit::STATUS_IN_PROGRESS])
            ->with([
                'patient:id,full_name,file_number,phone',
                'doctor:id,full_name',
            ])
            ->orderByRaw("CASE WHEN status = '".Visit::STATUS_IN_PROGRESS."' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->limit(40)
            ->get();

        $recentPatients = Patient::query()
            ->select(['id', 'full_name', 'phone', 'file_number', 'created_at'])
            ->latest()
            ->take(8)
            ->get();

        $openInvoicesCount = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        $clinic = ClinicSettings::current();

        $viewData = [
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'waitingQueue' => $waitingQueue,
            'recentPatients' => $recentPatients,
            'openInvoicesCount' => $openInvoicesCount,
            'todayLabel' => now()->translatedFormat('l d F Y'),
            'pageTitle' => __('dashboard.reception_title'),
        ];

        if ($request->ajax()) {
            return view('dashboards.partials.reception', $viewData);
        }

        return view('dashboards.reception', $viewData);
    }
}
