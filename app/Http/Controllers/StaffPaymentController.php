<?php

namespace App\Http\Controllers;

use App\Enums\PaymentCycle;
use App\Models\Staff;
use App\Models\StaffCompensationProfile;
use App\Models\StaffPayment;
use App\Support\AuditLogger;
use App\Support\Queries\StaffPaymentListQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffPaymentController extends Controller
{
    public function index(Request $request)
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $paymentBase = StaffPayment::query();

        $paymentStats = [
            'month_paid' => (float) (clone $paymentBase)
                ->whereBetween('period_end', [$monthStart, $monthEnd])
                ->sum('paid_amount'),
            'month_due' => (float) (clone $paymentBase)
                ->whereBetween('period_end', [$monthStart, $monthEnd])
                ->sum('total_due'),
            'outstanding' => (clone $paymentBase)->where('remaining_amount', '>', 0.01)->count(),
            'month_count' => (clone $paymentBase)
                ->whereBetween('period_end', [$monthStart, $monthEnd])
                ->count(),
        ];

        $payments = StaffPaymentListQuery::apply(
            StaffPayment::query()->with(['staff', 'creator', 'payrollRun', 'compensationProfile']),
            $request,
        )
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $staffList = Staff::query()->orderBy('full_name')->get();
        $roleTypeOptions = Staff::roleTypeOptions();

        $pageTitle = __('payroll.page_title_index');

        $viewData = compact('payments', 'staffList', 'roleTypeOptions', 'pageTitle', 'paymentStats');

        if ($request->ajax()) {
            return view('staff-payments.partials.content', $viewData);
        }

        return view('staff-payments.index', $viewData);
    }

    public function create(Request $request)
    {
        $staffList = Staff::query()->orderBy('full_name')->get();
        $compensationProfiles = StaffCompensationProfile::query()
            ->with('staff:id,full_name')
            ->orderBy('staff_id')
            ->get();

        $pageTitle = __('payroll.page_title_create');

        if ($request->ajax()) {
            return view('staff-payments.partials.create', compact('staffList', 'compensationProfiles', 'pageTitle'));
        }

        return view('staff-payments.create', compact('staffList', 'compensationProfiles', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        $totals = StaffPayment::computeTotals(
            (float) $validated['base_amount'],
            (float) $validated['bonus'],
            (float) $validated['deduction'],
            (float) $validated['paid_amount'],
        );

        DB::transaction(function () use ($validated, $totals) {
            $payment = StaffPayment::query()->create([
                ...$validated,
                'total_due' => $totals['total_due'],
                'remaining_amount' => $totals['remaining_amount'],
                'created_by' => auth()->id(),
            ]);

            AuditLogger::log(
                'staff_payment',
                'staff_payments',
                $payment->id,
                __('payroll.audit_create', ['staff_id' => $payment->staff_id, 'period' => $payment->periodLabel()]),
                null,
                [
                    'staff_id' => $payment->staff_id,
                    'period_type' => $payment->period_type->value,
                    'period_start' => $payment->period_start?->format('Y-m-d'),
                    'period_end' => $payment->period_end?->format('Y-m-d'),
                    'base_amount' => (string) $payment->base_amount,
                    'bonus' => (string) $payment->bonus,
                    'deduction' => (string) $payment->deduction,
                    'total_due' => (string) $payment->total_due,
                    'paid_amount' => (string) $payment->paid_amount,
                    'remaining_amount' => (string) $payment->remaining_amount,
                    'payment_date' => $payment->payment_date?->format('Y-m-d'),
                    'payment_method' => $payment->payment_method,
                ]
            );
        });

        return redirect()
            ->route('staff-payments.index')
            ->with('success', __('payroll.flash_created'));
    }

    public function edit(Request $request, StaffPayment $staff_payment)
    {
        $staff_payment->load(['staff']);
        $staffList = Staff::query()->orderBy('full_name')->get();
        $compensationProfiles = StaffCompensationProfile::query()
            ->with('staff:id,full_name')
            ->orderBy('staff_id')
            ->get();

        $pageTitle = __('payroll.page_title_edit');

        if ($request->ajax()) {
            return view('staff-payments.partials.edit', compact('staff_payment', 'staffList', 'compensationProfiles', 'pageTitle'));
        }

        return view('staff-payments.edit', compact('staff_payment', 'staffList', 'compensationProfiles', 'pageTitle'));
    }

    public function update(Request $request, StaffPayment $staff_payment)
    {
        $validated = $this->validatedPayload($request, $staff_payment->id);

        $totals = StaffPayment::computeTotals(
            (float) $validated['base_amount'],
            (float) $validated['bonus'],
            (float) $validated['deduction'],
            (float) $validated['paid_amount'],
        );

        DB::transaction(function () use ($staff_payment, $validated, $totals) {
            $before = $staff_payment->only([
                'staff_id', 'period_type', 'period_start', 'period_end', 'base_amount', 'bonus', 'deduction',
                'total_due', 'paid_amount', 'remaining_amount', 'payment_date', 'payment_method', 'notes',
                'compensation_profile_id', 'days_worked', 'source_type',
            ]);

            $staff_payment->update([
                ...$validated,
                'total_due' => $totals['total_due'],
                'remaining_amount' => $totals['remaining_amount'],
            ]);

            AuditLogger::log(
                'staff_payment',
                'staff_payments',
                $staff_payment->id,
                __('payroll.audit_update', ['id' => $staff_payment->id]),
                $before,
                $staff_payment->only([
                    'staff_id', 'period_type', 'period_start', 'period_end', 'base_amount', 'bonus', 'deduction',
                    'total_due', 'paid_amount', 'remaining_amount', 'payment_date', 'payment_method', 'notes',
                    'compensation_profile_id', 'days_worked', 'source_type',
                ])
            );
        });

        return redirect()
            ->route('staff-payments.index')
            ->with('success', __('payroll.flash_updated'));
    }

    public function destroy(StaffPayment $staff_payment)
    {
        DB::transaction(function () use ($staff_payment) {
            $id = $staff_payment->id;
            $snapshot = $staff_payment->only([
                'staff_id', 'period_type', 'period_start', 'period_end', 'base_amount', 'bonus', 'deduction',
                'total_due', 'paid_amount', 'remaining_amount', 'payment_date', 'payment_method', 'notes',
            ]);

            $staff_payment->delete();

            AuditLogger::log(
                'staff_payment',
                'staff_payments',
                $id,
                __('payroll.audit_delete', ['id' => $id]),
                $snapshot,
                null
            );
        });

        return redirect()
            ->route('staff-payments.index')
            ->with('success', __('payroll.flash_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'payment_method' => $request->filled('payment_method') ? $request->input('payment_method') : null,
            'compensation_profile_id' => $request->filled('compensation_profile_id') ? $request->integer('compensation_profile_id') : null,
            'source_type' => $request->filled('source_type') ? $request->input('source_type') : 'manual',
        ]);

        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'period_type' => ['required', Rule::in(PaymentCycle::values())],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'compensation_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('staff_compensation_profiles', 'id')->where(
                    fn ($q) => $q->where('staff_id', $request->integer('staff_id'))
                ),
            ],
            'days_worked' => ['nullable', 'integer', 'min:0', 'max:400'],
            'base_amount' => ['required', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:32', Rule::in(['cash', 'card', 'bank_transfer', 'other'])],
            'source_type' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['bonus'] = $validated['bonus'] ?? 0;
        $validated['deduction'] = $validated['deduction'] ?? 0;

        $base = (float) $validated['base_amount'];
        $bonus = (float) $validated['bonus'];
        $deduction = (float) $validated['deduction'];
        $paid = (float) $validated['paid_amount'];

        if ($base + $bonus < $deduction) {
            throw ValidationException::withMessages([
                'deduction' => __('payroll.validation_deduction_vs_base_bonus'),
            ]);
        }

        $totalDue = round($base + $bonus - $deduction, 2);
        $remaining = round($totalDue - $paid, 2);

        if ($paid - $totalDue > 0.0000001) {
            throw ValidationException::withMessages([
                'paid_amount' => __('payroll.validation_paid_exceeds_due'),
            ]);
        }

        if ($remaining < -0.0000001) {
            throw ValidationException::withMessages([
                'paid_amount' => __('payroll.validation_negative_remaining'),
            ]);
        }

        $duplicate = StaffPayment::query()
            ->where('staff_id', $validated['staff_id'])
            ->whereDate('period_start', $request->date('period_start'))
            ->whereDate('period_end', $request->date('period_end'))
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'period_start' => __('payroll.validation_duplicate_period'),
            ]);
        }

        return $validated;
    }
}
