<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Queries\StaffListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
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

    public function create(Request $request): View
    {
        $linkableUsersQuery = User::query()->orderBy('name');
        if (! Auth::user()?->hasRole('super_admin')) {
            $linkableUsersQuery->where('clinic_id', Auth::user()?->clinic_id);
        }
        $linkableUsers = $linkableUsersQuery->get(['id', 'name', 'email']);

        $pageTitle = __('staff.page_create');

        if ($request->ajax()) {
            return view('staff.partials.create', compact('linkableUsers', 'pageTitle'));
        }

        return view('staff.create', compact('linkableUsers', 'pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userExists = Rule::exists('users', 'id');
        if (! Auth::user()?->hasRole('super_admin') && Auth::user()?->clinic_id) {
            $userExists = Rule::exists('users', 'id')->where(fn ($q) => $q->where('clinic_id', Auth::user()->clinic_id));
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'string', Rule::in(Staff::ROLE_TYPES)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'user_id' => ['nullable', 'integer', $userExists, Rule::unique('staff', 'user_id')],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['user_id'] = $request->filled('user_id') ? $request->integer('user_id') : null;

        Staff::query()->create($validated);

        return redirect()->route('staff.index')
            ->with('success', 'تم إضافة الموظف بنجاح.');
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

        $linkableUsers = $linkableUsersQuery->get(['id', 'name', 'email']);
        $staff->load('compensationProfile');

        $pageTitle = __('staff.page_edit');

        if ($request->ajax()) {
            return view('staff.partials.edit', compact('staff', 'linkableUsers', 'pageTitle'));
        }

        return view('staff.edit', compact('staff', 'linkableUsers', 'pageTitle'));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $userExists = Rule::exists('users', 'id');
        if (! Auth::user()?->hasRole('super_admin') && Auth::user()?->clinic_id) {
            $userExists = Rule::exists('users', 'id')->where(fn ($q) => $q->where('clinic_id', Auth::user()->clinic_id));
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'string', Rule::in(Staff::ROLE_TYPES)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'user_id' => ['nullable', 'integer', $userExists, Rule::unique('staff', 'user_id')->ignore($staff->id)],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['user_id'] = $request->filled('user_id') ? $request->integer('user_id') : null;

        $staff->update($validated);

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
}
