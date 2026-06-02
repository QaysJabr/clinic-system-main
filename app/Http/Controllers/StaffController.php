<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use App\Services\StaffDoctorSyncService;
use App\Support\AuditLogger;
use App\Support\Queries\StaffListQuery;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffDoctorSyncService $staffDoctorSync,
    ) {}

    public function index(Request $request): View
    {
        $scope = Staff::query();

        $staffStats = [
            'total' => (clone $scope)->count(),
            'active' => (clone $scope)->where('status', 'active')->count(),
            'inactive' => (clone $scope)->where('status', 'inactive')->count(),
            'with_profile' => (clone $scope)->whereHas('compensationProfile')->count(),
        ];

        $staffMembers = StaffListQuery::apply(
            $scope->with([
                'user:id,name,email',
                'compensationProfile',
            ]),
            $request
        )
            ->orderBy('full_name')
            ->paginate(15)
            ->appends($request->query());

        $pageTitle = __('staff.page_list');

        if ($request->ajax()) {
            return view('staff.partials.content', compact('staffMembers', 'pageTitle', 'staffStats'));
        }

        return view('staff.index', compact('staffMembers', 'pageTitle', 'staffStats'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->query('role') === 'doctor') {
            return redirect()->route('doctors.onboarding.create');
        }

        $linkableUsersQuery = User::query()->orderBy('name');
        if (! Auth::user()?->hasRole('super_admin')) {
            $linkableUsersQuery->where('clinic_id', Auth::user()?->clinic_id);
        }
        $linkableUsers = $linkableUsersQuery->get(['id', 'name', 'email']);

        $pageTitle = __('staff.page_create');
        $includeDoctorRole = false;

        if ($request->ajax()) {
            return view('staff.partials.create', compact('linkableUsers', 'pageTitle', 'includeDoctorRole'));
        }

        return view('staff.create', compact('linkableUsers', 'pageTitle', 'includeDoctorRole'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->input('role_type') === 'doctor') {
            return redirect()
                ->route('doctors.onboarding.create')
                ->with('info', __('staff.doctor_use_onboarding'));
        }

        $validated = $this->normalizeStaffPayload($this->validateStaff($request));
        $validated['user_id'] = $request->filled('user_id') ? $request->integer('user_id') : null;

        Staff::query()->create($validated);

        return redirect()->route('staff.index')
            ->with('success', __('staff.flash_created'));
    }

    public function edit(Request $request, Staff $staff): View
    {
        $takenUserIds = Staff::query()
            ->where('id', '!=', $staff->id)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $linkableUsersQuery = User::query()
            ->where(function ($q) use ($takenUserIds, $staff) {
                $q->whereNotIn('id', $takenUserIds);
                if ($staff->user_id) {
                    $q->orWhere('id', $staff->user_id);
                }
            })
            ->orderBy('name');

        if (! Auth::user()?->hasRole('super_admin')) {
            $linkableUsersQuery->where('clinic_id', Auth::user()?->clinic_id);
        }

        if ($staff->role_type === 'doctor') {
            $linkableUsersQuery->role('doctor');
        }

        $linkableUsers = $linkableUsersQuery->get(['id', 'name', 'email']);
        $staff->load(['compensationProfile']);
        $resolvedDoctor = $this->staffDoctorSync->resolveDoctorForStaff($staff);
        $staff->setRelation('doctor', $resolvedDoctor->exists ? $resolvedDoctor : null);

        $pageTitle = __('staff.page_edit');
        $includeDoctorRole = true;

        if ($request->ajax()) {
            return view('staff.partials.edit', compact('staff', 'linkableUsers', 'pageTitle', 'includeDoctorRole'));
        }

        return view('staff.edit', compact('staff', 'linkableUsers', 'pageTitle', 'includeDoctorRole'));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $this->normalizeStaffPayload($this->validateStaff($request, $staff));
        $validated['user_id'] = $request->filled('user_id') ? $request->integer('user_id') : null;

        $wasDoctor = $staff->role_type === 'doctor';
        $staff->update($validated);
        $staff->refresh();

        if ($staff->role_type === 'doctor') {
            $this->staffDoctorSync->syncDoctorForStaff(
                $staff,
                $this->staffDoctorSync->doctorFieldsFromRequest($request)
            );

            return redirect()->route('staff.index')
                ->with('success', $wasDoctor ? __('staff.flash_updated') : __('staff.flash_updated_doctor_linked'));
        }

        if ($wasDoctor) {
            $this->staffDoctorSync->removeDoctorForStaff($staff);
        }

        return redirect()->route('staff.index')
            ->with('success', __('staff.flash_updated'));
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $id = $staff->id;
        $name = $staff->full_name;
        $snapshot = [
            'full_name' => $staff->full_name,
            'role_type' => $staff->role_type,
            'user_id' => $staff->user_id,
        ];

        if ($staff->role_type === 'doctor') {
            $this->staffDoctorSync->removeDoctorForStaff($staff);
        }

        $staff->delete();

        AuditLogger::log(
            'delete',
            'staff',
            $id,
            'حذف موظف: '.$name,
            $snapshot,
            null
        );

        return redirect()->route('staff.index')
            ->with('success', __('staff.flash_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateStaff(Request $request, ?Staff $staff = null): array
    {
        $userExists = Rule::exists('users', 'id');
        if (! Auth::user()?->hasRole('super_admin') && Auth::user()?->clinic_id) {
            $userExists = Rule::exists('users', 'id')->where(fn ($q) => $q->where('clinic_id', Auth::user()->clinic_id));
        }

        $clinicId = TenantValidation::clinicIdForRules();

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'string', Rule::in(Staff::ROLE_TYPES)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'user_id' => ['nullable', 'integer', $userExists, Rule::unique('staff', 'user_id')->ignore($staff?->id)],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];

        if ($request->input('role_type') === 'doctor') {
            $ignoreDoctorId = $staff
                ? Doctor::query()->withoutGlobalScopes()->where('staff_id', $staff->id)->value('id')
                : null;

            $rules['specialty'] = ['nullable', 'string', 'max:255'];
            $rules['room_number'] = ['nullable', 'string', 'max:50'];
            $rules['notes'] = ['nullable', 'string', 'max:5000'];

            $licenseRules = ['nullable', 'string', 'max:255'];
            if (filled($request->input('license_number'))) {
                $licenseRules[] = Rule::unique('doctors', 'license_number')
                    ->where(fn ($q) => $q->where('clinic_id', $clinicId))
                    ->ignore($ignoreDoctorId);
            }
            $rules['license_number'] = $licenseRules;
        }

        return $request->validate($rules);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeStaffPayload(array $validated): array
    {
        foreach (['phone', 'email'] as $key) {
            if (array_key_exists($key, $validated) && ! filled($validated[$key])) {
                $validated[$key] = null;
            }
        }

        return $validated;
    }
}
