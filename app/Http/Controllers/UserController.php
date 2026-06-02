<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Staff;
use App\Models\User;
use App\Services\UserStaffLinkService;
use App\Support\AuditLogger;
use App\Support\Queries\UserListQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserStaffLinkService $userStaffLink,
    ) {}

    private const CLINIC_ASSIGNABLE_ROLES = [
        'admin',
        'clinic_owner',
        'doctor',
        'receptionist',
        'accountant',
    ];

    public function index(Request $request): View
    {
        $usersQuery = User::query()
            ->with('roles')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin')->where('guard_name', 'web'));

        if (! Auth::user()?->hasRole('super_admin')) {
            $usersQuery->where('clinic_id', Auth::user()?->clinic_id);
        }

        $userStats = [
            'total' => (clone $usersQuery)->count(),
            'admins' => (clone $usersQuery)->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'clinic_owner'])->where('guard_name', 'web'))->count(),
            'doctors' => (clone $usersQuery)->whereHas('roles', fn ($q) => $q->where('name', 'doctor')->where('guard_name', 'web'))->count(),
            'staff' => (clone $usersQuery)->whereHas('roles', fn ($q) => $q->whereIn('name', ['receptionist', 'accountant'])->where('guard_name', 'web'))->count(),
        ];

        $roleOptions = $this->assignableRoleNames();

        $users = UserListQuery::apply(clone $usersQuery, $request)
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        $pageTitle = __('settings.users_page_title_index');

        if ($request->ajax()) {
            return view('users.partials.content', compact('users', 'pageTitle', 'userStats', 'roleOptions'));
        }

        return view('users.index', compact('users', 'pageTitle', 'userStats', 'roleOptions'));
    }

    public function create(Request $request): View
    {
        $roles = $this->assignableWebRoles();
        $linkableStaff = $this->linkableStaffWithoutUser();
        $pageTitle = __('settings.users_create_heading');
        $defaultMode = $linkableStaff->isNotEmpty() ? 'from_staff' : 'new';

        if ($request->ajax()) {
            return view('users.partials.create', compact('roles', 'pageTitle', 'linkableStaff', 'defaultMode'));
        }

        return view('users.create', compact('roles', 'pageTitle', 'linkableStaff', 'defaultMode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $mode = $request->input('account_mode', 'from_staff');

        if ($mode === 'from_staff') {
            return $this->storeFromStaff($request);
        }

        if ($request->input('role') === 'doctor') {
            return redirect()
                ->route('doctors.onboarding.create')
                ->with('info', __('settings.users_doctor_use_onboarding'));
        }

        return $this->storeNewAccount($request);
    }

    private function storeFromStaff(Request $request): RedirectResponse
    {
        $clinicId = Auth::user()?->clinic_id ?? config('tenancy.default_clinic_id');

        $staffExists = Rule::exists('staff', 'id')->whereNull('user_id');
        if (! Auth::user()?->hasRole('super_admin') && $clinicId) {
            $staffExists = Rule::exists('staff', 'id')
                ->where(fn ($q) => $q->where('clinic_id', $clinicId)->whereNull('user_id'));
        }

        $validated = $request->validate([
            'account_mode' => ['required', Rule::in(['from_staff'])],
            'staff_id' => ['required', 'integer', $staffExists],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'nullable',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
                Rule::in($this->assignableRoleNames()),
            ],
        ]);

        $this->assertCanAddUser();

        $staff = Staff::query()->whereKey($validated['staff_id'])->firstOrFail();
        $role = $validated['role'] ?? UserStaffLinkService::roleForStaff($staff);

        $user = $this->userStaffLink->createUserForStaff(
            $staff,
            $validated['password'],
            $role
        );

        $user->load('roles');

        AuditLogger::log(
            'create',
            'users',
            $user->id,
            __('settings.audit_user_create_from_staff', [
                'email' => $user->email,
                'staff' => $staff->full_name,
            ]),
            null,
            $this->userAuditSnapshot($user)
        );

        return redirect()
            ->route('users.index')
            ->with('success', __('settings.users_flash_linked_staff', ['name' => $staff->full_name]));
    }

    private function storeNewAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_mode' => ['required', Rule::in(['new'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
                Rule::in($this->assignableRoleNames()),
            ],
        ]);

        $this->assertCanAddUser();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'clinic_id' => Auth::user()?->clinic_id ?? config('tenancy.default_clinic_id'),
        ]);

        $user->syncRoles([$validated['role']]);
        $user->load('roles');

        AuditLogger::log(
            'create',
            'users',
            $user->id,
            __('settings.audit_user_create', ['email' => $user->email]),
            null,
            $this->userAuditSnapshot($user)
        );

        return redirect()->route('users.index')->with('success', __('settings.users_flash_created'));
    }

    private function assertCanAddUser(): void
    {
        $clinicId = Auth::user()?->clinic_id ?? config('tenancy.default_clinic_id');
        $clinic = $clinicId ? Clinic::query()->with('plan')->find($clinicId) : null;

        if ($clinic && ! Auth::user()?->hasRole('super_admin') && ! $clinic->canAddUser()) {
            throw ValidationException::withMessages([
                'email' => __('settings.users_error_plan_limit'),
            ]);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Staff>
     */
    private function linkableStaffWithoutUser()
    {
        $query = Staff::query()
            ->whereNull('user_id')
            ->orderBy('full_name');

        if (! Auth::user()?->hasRole('super_admin') && Auth::user()?->clinic_id) {
            $query->where('clinic_id', Auth::user()->clinic_id);
        }

        return $query->get(['id', 'full_name', 'email', 'phone', 'role_type']);
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeManagedUser($user);

        $roles = $this->assignableWebRoles($user);
        $user->load('roles');

        $pageTitle = __('settings.users_edit_heading');

        if ($request->ajax()) {
            return view('users.partials.edit', compact('user', 'roles', 'pageTitle'));
        }

        return view('users.edit', compact('user', 'roles', 'pageTitle'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagedUser($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
                Rule::in($this->assignableRoleNames()),
            ],
        ]);

        if ($user->hasRole('admin') && $validated['role'] !== 'admin') {
            $otherAdmins = User::query()
                ->role('admin')
                ->where('id', '!=', $user->id)
                ->where('clinic_id', $user->clinic_id)
                ->count();

            if ($otherAdmins === 0) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'role' => __('settings.users_error_cannot_demote_last_admin'),
                    ]);
            }
        }

        $user->load('roles');
        $old = $this->userAuditSnapshot($user);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->syncRoles([$validated['role']]);
        $user->refresh();
        $user->load('roles');

        $newSnapshot = $this->userAuditSnapshot($user);

        if (($old['role'] ?? null) !== ($newSnapshot['role'] ?? null)) {
            AuditLogger::security(
                'role_change',
                'users',
                $user->id,
                'تغيير دور المستخدم: '.$user->email,
                ['role' => $old['role'] ?? null],
                ['role' => $newSnapshot['role'] ?? null],
            );
        }

        AuditLogger::log(
            'update',
            'users',
            $user->id,
            __('settings.audit_user_update', ['email' => $user->email]),
            $old,
            $newSnapshot
        );

        if ($validated['role'] === 'doctor' && ! $user->staffRecord()->exists()) {
            return redirect()
                ->route('doctors.onboarding.create', [
                    'account_mode' => 'existing',
                    'user_id' => $user->id,
                    'name' => $user->name,
                ])
                ->with('info', __('doctor_onboarding.complete_profile'));
        }

        return redirect()->route('users.index')->with('success', __('settings.users_flash_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeManagedUser($user);

        $auth = Auth::user();
        if (! $auth) {
            abort(403);
        }

        if ($user->id === $auth->id) {
            return redirect()->route('users.index')
                ->with('error', __('settings.users_error_cannot_delete_self'));
        }

        if ($this->isPlatformOwnerAccount($user)) {
            return redirect()->route('users.index')
                ->with('error', __('settings.users_error_cannot_delete_platform_owner'));
        }

        if ($user->hasRole('admin') && $user->clinic_id) {
            $otherAdmins = User::query()
                ->role('admin')
                ->where('clinic_id', $user->clinic_id)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                return redirect()->route('users.index')
                    ->with('error', __('settings.users_error_cannot_delete_last_admin'));
            }
        }

        $id = $user->id;
        $email = $user->email;
        $snapshot = $this->userAuditSnapshot($user);

        $user->syncRoles([]);
        $user->deleteStoredAvatar();
        $user->delete();

        AuditLogger::log(
            'delete',
            'users',
            $id,
            __('settings.audit_user_delete', ['email' => $email]),
            $snapshot,
            null
        );

        return redirect()->route('users.index')
            ->with('success', __('settings.users_flash_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function userAuditSnapshot(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }

    /**
     * أدوار عيادات فقط (دون super_admin) لإدارة المستخدمين اليومية.
     *
     * @return Collection<int, Role>
     */
    private function assignableWebRoles(?User $editedUser = null)
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $this->assignableRoleNames())
            ->orderBy('name')
            ->get();
    }

    private function authorizeManagedUser(User $user): void
    {
        $auth = Auth::user();

        if (! $auth) {
            abort(403);
        }

        if ($auth->hasRole('super_admin')) {
            if ($this->isPlatformOwnerAccount($user) && (int) $auth->id !== (int) $user->id) {
                abort(403);
            }

            return;
        }

        if ($user->hasRole('super_admin')) {
            abort(403);
        }

        if ((int) ($user->clinic_id ?? 0) !== (int) ($auth->clinic_id ?? 0)) {
            abort(403);
        }
    }

    /**
     * @return list<string>
     */
    private function assignableRoleNames(): array
    {
        return self::CLINIC_ASSIGNABLE_ROLES;
    }

    private function isPlatformOwnerAccount(User $user): bool
    {
        $ownerEmail = config('platform.owner_email');
        if (! is_string($ownerEmail) || $ownerEmail === '') {
            return false;
        }

        return strcasecmp($user->email, $ownerEmail) === 0;
    }
}
