<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DoctorOnboardingService;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class DoctorOnboardingController extends Controller
{
    public function __construct(
        private readonly DoctorOnboardingService $onboarding,
    ) {}

    public function create(Request $request): View
    {
        $linkableUsers = $this->linkableUsersWithoutStaff();

        $pageTitle = __('doctor_onboarding.page_title');

        return view('doctors.onboarding.create', compact('linkableUsers', 'pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $result = $this->onboarding->create($validated, $request->user());

        return redirect()
            ->route('doctors.edit', $result['doctor'])
            ->with('success', __('doctor_onboarding.flash_created', ['name' => $result['doctor']->full_name]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        $clinicId = TenantValidation::clinicIdForRules();
        $mode = $request->input('account_mode', 'new');

        $userExists = Rule::exists('users', 'id');
        if (! Auth::user()?->hasRole('super_admin') && Auth::user()?->clinic_id) {
            $userExists = Rule::exists('users', 'id')->where(fn ($q) => $q->where('clinic_id', Auth::user()->clinic_id));
        }

        $rules = [
            'account_mode' => ['required', Rule::in(['new', 'existing'])],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => [
                'nullable',
                'string',
                Rule::unique('doctors', 'license_number')->where(fn ($q) => $q->where('clinic_id', $clinicId)),
            ],
            'room_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];

        if ($mode === 'existing') {
            $rules['user_id'] = ['required', 'integer', $userExists, Rule::unique('staff', 'user_id')];
        } else {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:'.User::class];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);
        $validated['account_mode'] = $mode;

        return $validated;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function linkableUsersWithoutStaff()
    {
        $query = User::query()
            ->role('doctor')
            ->whereDoesntHave('staffRecord')
            ->orderBy('name');

        if (! Auth::user()?->hasRole('super_admin') && Auth::user()?->clinic_id) {
            $query->where('clinic_id', Auth::user()->clinic_id);
        }

        return $query->get(['id', 'name', 'email']);
    }
}
