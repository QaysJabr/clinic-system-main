<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Security\TwoFactorService;
use App\Services\Security\UserSessionService;
use App\Support\AuditLogger;
use App\Support\AuditLogLabels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('roles');

        $twoFactor = app(TwoFactorService::class);
        $activeSessions = app(UserSessionService::class)->activeSessionsFor($user, $request->session()->getId());

        $recentAuditLogs = $user->hasRole('super_admin')
            ? collect()
            : AuditLog::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(12)
                ->get(['id', 'action', 'module', 'record_id', 'description', 'created_at']);

        $activityCount = $user->hasRole('super_admin')
            ? 0
            : AuditLog::query()->where('user_id', $user->id)->count();

        $pageTitle = __('profile.page_title');

        $viewData = [
            'user' => $user,
            'recentAuditLogs' => $recentAuditLogs,
            'auditLogModules' => AuditLogLabels::modules(),
            'auditLogActions' => AuditLogLabels::actions(),
            'profileStats' => [
                'email_verified' => $user->hasVerifiedEmail(),
                'two_factor' => $twoFactor->isEnabled($user),
                'sessions' => $activeSessions->count(),
                'activity' => $activityCount,
            ],
            'pageTitle' => $pageTitle,
        ];

        if ($request->ajax()) {
            return view('profile.partials.edit', $viewData);
        }

        return view('profile.edit', $viewData);
    }

    /**
     * عرض الصورة الشخصية من التخزين دون الاعتماد على symlink لـ public/storage.
     */
    public function avatar(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();
        abort_unless($viewer, 403);

        if ((int) $viewer->id !== (int) $user->id) {
            $viewerClinic = $viewer->clinic_id;
            $targetClinic = $user->clinic_id;
            abort_unless(
                $viewerClinic !== null && $targetClinic !== null && (int) $viewerClinic === (int) $targetClinic,
                403
            );
        }

        $relative = $user->normalizedAvatarRelativePath();
        abort_unless($relative !== null && Storage::disk('public')->exists($relative), 404);

        return Storage::disk('public')->response($relative);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'has_avatar' => (bool) $user->avatar_path,
        ];

        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            $user->deleteStoredAvatar();
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $user->deleteStoredAvatar();
            $user->avatar_path = null;
        }

        $user->save();

        if ($user->wasChanged(['name', 'email', 'avatar_path'])) {
            AuditLogger::log(
                'update',
                'profile',
                $user->id,
                __('profile.audit_profile_updated'),
                [
                    'name' => $before['name'],
                    'email' => $before['email'],
                    'has_avatar' => $before['has_avatar'],
                ],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'has_avatar' => (bool) $user->avatar_path,
                ],
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        AuditLogger::log(
            'delete',
            'profile',
            $user->id,
            __('profile.audit_account_deleted', ['email' => $user->email]),
            [
                'name' => $user->name,
                'email' => $user->email,
            ],
            null
        );

        $user->deleteStoredAvatar();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
