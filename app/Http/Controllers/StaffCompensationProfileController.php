<?php

namespace App\Http\Controllers;

use App\Enums\PaymentCycle;
use App\Enums\StaffCompensationModel;
use App\Models\Staff;
use App\Models\StaffCompensationProfile;
use App\Support\AuditLogger;
use App\Support\Queries\StaffCompensationProfileListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffCompensationProfileController extends Controller
{
    public function index(Request $request): View
    {
        $base = StaffCompensationProfile::query();

        $profileStats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', StaffCompensationProfile::STATUS_ACTIVE)->count(),
            'inactive' => (clone $base)->where('status', StaffCompensationProfile::STATUS_INACTIVE)->count(),
            'unassigned' => Staff::query()->whereDoesntHave('compensationProfile')->count(),
        ];

        $profiles = StaffCompensationProfileListQuery::apply(
            StaffCompensationProfile::query()
                ->with([
                    'staff' => fn ($q) => $q->select('id', 'full_name', 'role_type', 'user_id', 'email'),
                    'staff.user' => fn ($q) => $q->select('id', 'name')->with('roles'),
                ])
                ->orderByDesc('updated_at'),
            $request,
        )->paginate(15)->withQueryString();

        $pageTitle = __('staff.comp_profiles_title');

        $viewData = compact('profiles', 'pageTitle', 'profileStats');

        if ($request->ajax()) {
            return view('staff-compensation-profiles.partials.content', $viewData);
        }

        return view('staff-compensation-profiles.index', $viewData);
    }

    public function create(Request $request): View
    {
        $existingStaffIds = StaffCompensationProfile::query()->pluck('staff_id');
        $staffList = Staff::query()
            ->whereNotIn('id', $existingStaffIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'role_type', 'email']);

        $pageTitle = __('staff.comp_profile_new_title');

        if ($request->ajax()) {
            return view('staff-compensation-profiles.partials.create', compact('staffList', 'pageTitle'));
        }

        return view('staff-compensation-profiles.create', compact('staffList', 'pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $type = (string) $request->input('compensation_type');

        $validated = $request->validate(array_merge([
            'staff_id' => ['required', 'exists:staff,id', Rule::unique('staff_compensation_profiles', 'staff_id')],
            'compensation_type' => ['required', Rule::in(StaffCompensationModel::values())],
            'payment_cycle' => ['required', Rule::in(PaymentCycle::values())],
            'start_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ], $this->amountRulesForType($type)));

        $profile = new StaffCompensationProfile;
        $profile->staff_id = $validated['staff_id'];
        $profile->compensation_type = StaffCompensationModel::from($type);
        $profile->payment_cycle = PaymentCycle::from($validated['payment_cycle']);
        $profile->start_date = $validated['start_date'];
        $profile->status = $validated['status'];
        $profile->notes = $validated['notes'] ?? null;
        $this->applyAmountFields($profile, StaffCompensationModel::from($type), $request);

        $profile->save();

        AuditLogger::log(
            'create',
            'staff_compensation_profiles',
            $profile->id,
            __('staff.comp_profile_audit_create', ['id' => $profile->staff_id]),
            null,
            $this->profileAuditSnapshot($profile),
            StaffCompensationProfile::class
        );

        return redirect()->route('staff-compensation-profiles.index')
            ->with('success', __('staff.comp_profile_save_success'));
    }

    public function edit(Request $request, StaffCompensationProfile $staff_compensation_profile): View
    {
        $staff_compensation_profile->load([
            'staff' => fn ($q) => $q->select('id', 'full_name', 'role_type', 'email', 'user_id'),
            'staff.user' => fn ($q) => $q->select('id', 'name')->with('roles'),
        ]);

        $pageTitle = __('staff.comp_profile_edit_title');

        if ($request->ajax()) {
            return view('staff-compensation-profiles.partials.edit', ['profile' => $staff_compensation_profile, 'pageTitle' => $pageTitle]);
        }

        return view('staff-compensation-profiles.edit', ['profile' => $staff_compensation_profile, 'pageTitle' => $pageTitle]);
    }

    public function update(Request $request, StaffCompensationProfile $staff_compensation_profile): RedirectResponse
    {
        $type = (string) $request->input('compensation_type');

        $request->validate(array_merge([
            'compensation_type' => ['required', Rule::in(StaffCompensationModel::values())],
            'payment_cycle' => ['required', Rule::in(PaymentCycle::values())],
            'start_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ], $this->amountRulesForType($type)));

        $old = $this->profileAuditSnapshot($staff_compensation_profile);

        $staff_compensation_profile->compensation_type = StaffCompensationModel::from($type);
        $staff_compensation_profile->payment_cycle = PaymentCycle::from($request->input('payment_cycle'));
        $staff_compensation_profile->start_date = $request->date('start_date');
        $staff_compensation_profile->status = $request->input('status');
        $staff_compensation_profile->notes = $request->input('notes');
        $this->applyAmountFields($staff_compensation_profile, StaffCompensationModel::from($type), $request);

        $staff_compensation_profile->save();

        AuditLogger::log(
            'update',
            'staff_compensation_profiles',
            $staff_compensation_profile->id,
            __('staff.comp_profile_audit_update', ['id' => $staff_compensation_profile->id]),
            $old,
            $this->profileAuditSnapshot($staff_compensation_profile->fresh()),
            StaffCompensationProfile::class
        );

        return redirect()->route('staff-compensation-profiles.index')
            ->with('success', __('staff.comp_profile_update_success'));
    }

    /**
     * @return array<string, mixed>
     */
    private function profileAuditSnapshot(StaffCompensationProfile $p): array
    {
        return [
            'staff_id' => $p->staff_id,
            'compensation_type' => $p->compensation_type?->value,
            'payment_cycle' => $p->payment_cycle?->value,
            'percentage_rate' => $p->percentage_rate !== null ? (string) $p->percentage_rate : null,
            'base_salary' => $p->base_salary !== null ? (string) $p->base_salary : null,
            'daily_wage' => $p->daily_wage !== null ? (string) $p->daily_wage : null,
            'status' => $p->status,
            'start_date' => $p->start_date?->format('Y-m-d'),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function amountRulesForType(string $type): array
    {
        return match ($type) {
            StaffCompensationModel::Fixed->value => [
                'base_salary' => ['required', 'numeric', 'min:0'],
            ],
            StaffCompensationModel::Percentage->value => [
                'percentage_rate' => ['required', 'numeric', 'min:0', 'max:100'],
                'calculation_basis' => ['nullable', 'string', 'max:64', 'in:invoice_paid_total,gross_revenue'],
            ],
            StaffCompensationModel::Daily->value => [
                'daily_wage' => ['required', 'numeric', 'min:0'],
            ],
            default => [],
        };
    }

    private function applyAmountFields(
        StaffCompensationProfile $profile,
        StaffCompensationModel $type,
        Request $request
    ): void {
        $profile->base_salary = null;
        $profile->percentage_rate = null;
        $profile->daily_wage = null;
        $profile->calculation_basis = null;

        match ($type) {
            StaffCompensationModel::Fixed => $profile->base_salary = $request->input('base_salary'),
            StaffCompensationModel::Percentage => $this->applyPercentageAmounts($profile, $request),
            StaffCompensationModel::Daily => $profile->daily_wage = $request->input('daily_wage'),
        };
    }

    private function applyPercentageAmounts(StaffCompensationProfile $profile, Request $request): void
    {
        $profile->percentage_rate = $request->input('percentage_rate');
        $profile->calculation_basis = $request->filled('calculation_basis')
            ? (string) $request->input('calculation_basis')
            : 'invoice_paid_total';
    }
}
