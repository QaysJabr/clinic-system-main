<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

final class RegisterClinicController extends Controller
{
    public function create(): View
    {
        return view('saas.register-clinic');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms_accepted' => ['accepted'],
        ]);

        [$user, $clinic] = DB::transaction(function () use ($validated) {
            $clinic = Clinic::query()->create([
                'name' => $validated['clinic_name'],
                'owner_id' => null,
                'subscription_plan' => null,
                'subscription_status' => Clinic::STATUS_EXPIRED,
                'subscription_expires_at' => now()->subDay(),
                'is_active' => true,
            ]);

            $user = User::query()->create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'clinic_id' => $clinic->id,
            ]);

            // Ensure clinic owner role exists even if seeders were not run.
            $admin = Role::findOrCreate('admin', 'web');
            $user->assignRole($admin);

            $clinic->forceFill(['owner_id' => $user->id])->save();

            return [$user, $clinic];
        });

        if (config('security.email_verification.enabled', false)) {
            event(new Registered($user));
        }

        Auth::login($user);

        AuditLogger::log(
            'create',
            'clinics',
            $clinic->id,
            __('saas.audit_register_clinic', ['name' => $validated['clinic_name']]),
            null,
            ['owner_email' => $user->email]
        );

        $request->session()->put('show_onboarding', true);

        return redirect()
            ->route('saas.pricing')
            ->with('success', __('saas.flash_clinic_created_pick_plan'));
    }
}
