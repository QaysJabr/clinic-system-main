<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Support\ClinicSettings;
use App\Support\DoctorFinancialSummary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * لوحة الطبيب: زيارات اليوم، مستحقات مختصرة.
 */
final class ClinicalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $doctor = $user->linkedDoctor();

        $today = now()->toDateString();

        $todayVisits = collect();
        $financial = null;

        if ($user->hasRole('admin')) {
            $todayVisits = Visit::query()
                ->whereDate('visit_date', $today)
                ->with(['patient:id,full_name,file_number', 'doctor:id,full_name'])
                ->orderByRaw("CASE WHEN status = '".Visit::STATUS_WAITING."' THEN 0 WHEN status = '".Visit::STATUS_IN_PROGRESS."' THEN 1 ELSE 2 END")
                ->orderBy('id')
                ->limit(80)
                ->get();
        } elseif ($doctor) {
            $todayVisits = Visit::query()
                ->where('doctor_id', $doctor->id)
                ->whereDate('visit_date', $today)
                ->with(['patient:id,full_name,file_number'])
                ->orderByRaw("CASE WHEN status = '".Visit::STATUS_WAITING."' THEN 0 WHEN status = '".Visit::STATUS_IN_PROGRESS."' THEN 1 ELSE 2 END")
                ->orderBy('id')
                ->limit(50)
                ->get();

            if ($user->can('view doctor earnings')) {
                $financial = DoctorFinancialSummary::forDoctor((int) $doctor->id);
            }
        }

        $clinic = ClinicSettings::current();

        $viewData = [
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'doctor' => $doctor,
            'todayVisits' => $todayVisits,
            'financial' => $financial,
            'todayLabel' => now()->translatedFormat('l d F Y'),
            'pageTitle' => __('dashboard.clinical_title'),
        ];

        if ($request->ajax()) {
            return view('dashboards.partials.clinical', $viewData);
        }

        return view('dashboards.clinical', $viewData);
    }
}
