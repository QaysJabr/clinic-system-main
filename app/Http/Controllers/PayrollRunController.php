<?php

namespace App\Http\Controllers;

use App\Enums\PaymentCycle;
use App\Models\PayrollRun;
use App\Services\PayrollRunGenerationService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollRunController extends Controller
{
    public function index(Request $request): View
    {
        $runs = PayrollRun::query()
            ->with(['generator:id,name'])
            ->withCount('staffPayments')
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->paginate(15);

        $pageTitle = __('payroll.runs_page_title');

        if ($request->ajax()) {
            return view('payroll-runs.partials.index', compact('runs', 'pageTitle'));
        }

        return view('payroll-runs.index', compact('runs', 'pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_type' => ['required', Rule::in(PaymentCycle::values())],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = PayrollRun::query()
            ->where('period_type', $validated['period_type'])
            ->whereDate('period_start', $request->date('period_start'))
            ->whereDate('period_end', $request->date('period_end'))
            ->exists();

        if ($exists) {
            return redirect()
                ->route('payroll-runs.index')
                ->withErrors(['period_start' => __('payroll.runs_validation_period_exists')])
                ->withInput();
        }

        PayrollRun::query()->create([
            'period_type' => $validated['period_type'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'status' => 'draft',
            'generated_at' => null,
            'generated_by' => null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('payroll-runs.index')
            ->with('success', __('payroll.runs_flash_period_saved_draft'));
    }

    public function generate(PayrollRun $payroll_run): RedirectResponse
    {
        $result = app(PayrollRunGenerationService::class)->generate($payroll_run, auth()->id());

        AuditLogger::log(
            'payroll_run_generate',
            'payroll_runs',
            $payroll_run->id,
            __('payroll.runs_audit_generate_summary', [
                'id' => $payroll_run->id,
                'created' => $result->created,
                'existing' => $result->skippedExisting,
                'inactive' => $result->skippedInactiveStaff,
                'before_profile' => $result->skippedBeforeProfileStart,
                'incomplete' => $result->skippedIncompleteProfile,
            ]),
            null,
            [
                'created' => $result->created,
                'skipped_existing' => $result->skippedExisting,
                'skipped_inactive_staff' => $result->skippedInactiveStaff,
                'skipped_before_profile_start' => $result->skippedBeforeProfileStart,
                'skipped_incomplete_profile' => $result->skippedIncompleteProfile,
            ]
        );

        return redirect()
            ->route('payroll-runs.index')
            ->with('success', __('payroll.runs_flash_generate_summary', [
                'created' => $result->created,
                'existing' => $result->skippedExisting,
                'inactive' => $result->skippedInactiveStaff,
                'before_profile' => $result->skippedBeforeProfileStart,
                'incomplete' => $result->skippedIncompleteProfile,
            ]));
    }
}
