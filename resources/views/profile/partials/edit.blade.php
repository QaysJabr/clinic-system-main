@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $profileStats ?? ['email_verified' => false, 'two_factor' => false, 'sessions' => 0, 'activity' => 0];
    $twoFactor = app(\App\Services\Security\TwoFactorService::class);
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('profile.page_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">
                @if($user->hasRole('super_admin'))
                    {{ __('profile.intro_super_admin') }}
                @else
                    {{ __('profile.intro_clinic') }}
                @endif
            </p>
        </div>
    </div>

    <x-profile.nav active="profile" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('profile.stat_email') }}</p>
            <p class="m-0 mt-1 text-lg font-bold {{ $stats['email_verified'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                {{ $stats['email_verified'] ? __('profile.stat_verified') : __('profile.stat_unverified') }}
            </p>
        </div>
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-900 dark:text-violet-300">{{ __('profile.stat_two_factor') }}</p>
            <p class="m-0 mt-1 text-lg font-bold {{ $stats['two_factor'] ? 'text-violet-950 dark:text-violet-100' : 'text-slate-600 dark:text-slate-400' }}">
                {{ $stats['two_factor'] ? __('profile.stat_enabled') : __('profile.stat_disabled') }}
            </p>
        </div>
        <div class="rounded-xl border border-cyan-200/90 bg-cyan-50/50 p-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-cyan-900 dark:text-cyan-300">{{ __('profile.stat_sessions') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-cyan-900 dark:text-cyan-200">{{ number_format($stats['sessions']) }}</p>
        </div>
        @unless($user->hasRole('super_admin'))
            <div class="rounded-xl border border-indigo-200/90 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
                <p class="m-0 text-xs font-bold uppercase tracking-wide text-indigo-900 dark:text-indigo-300">{{ __('profile.stat_activity') }}</p>
                <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-indigo-950 dark:text-indigo-100">{{ number_format($stats['activity']) }}</p>
            </div>
        @endunless
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-6 overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-800/50 dark:bg-emerald-950/35 dark:text-emerald-200" role="status">
            {{ __('profile.verification_link_sent') }}
        </div>
    @endif

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <p class="dash-section-label m-0">{{ __('profile.card_account') }}</p>
        </div>
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5 sm:py-5">
            <div class="flex items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-2xl object-cover ring-2 ring-slate-100 dark:ring-slate-600" width="64" height="64">
                <div class="min-w-0">
                    <p class="m-0 truncate text-lg font-bold text-gray-900 dark:text-[#F3F4F6]">{{ $user->name }}</p>
                    <p class="m-0 mt-1 truncate text-sm text-gray-500 dark:text-[#9CA3AF]">{{ $user->email }}</p>
                </div>
            </div>
            @if($user->roles->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($user->roles as $role)
                        <x-user.role-badge :role-name="$role->name" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @unless($user->hasRole('super_admin'))
        <div class="dash-section-group">
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="dash-section-label m-0">{{ __('profile.activity_heading') }}</p>
                    <p class="m-0 mt-1 text-sm text-gray-500 dark:text-[#9CA3AF]">{{ __('profile.activity_sub') }}</p>
                </div>
                @can('view audit logs')
                    <a href="{{ route('audit-logs.index', ['user_id' => $user->id]) }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">
                        {{ __('profile.activity_view_full') }}
                    </a>
                @endcan
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="overflow-x-auto">
                    @if($recentAuditLogs->isEmpty())
                        <p class="m-0 px-4 py-10 text-center text-sm text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('profile.activity_empty') }}</p>
                    @else
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                                    <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('profile.audit_col_action') }}</th>
                                    <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('profile.audit_col_module') }}</th>
                                    <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 min-w-[180px]">{{ __('profile.audit_col_description') }}</th>
                                    <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">{{ __('profile.audit_col_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAuditLogs as $log)
                                    <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                        <td class="px-4 py-3 sm:px-5">
                                            <x-audit.action-badge
                                                :action="$log->action"
                                                :label="$auditLogActions[$log->action] ?? $log->action"
                                            />
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ $auditLogModules[$log->module] ?? $log->module }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $log->description ?: __('common.em_dash') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endunless

    <div class="space-y-6">
        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('profile.section_profile') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                    <p class="m-0 text-sm text-gray-500 dark:text-[#9CA3AF]">{{ __('profile.section_profile_sub') }}</p>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('security.two_factor_title') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                    <p class="m-0 text-sm text-gray-500 dark:text-[#9CA3AF]">{{ __('security.two_factor_intro') }}</p>
                </div>
                <div class="space-y-4 p-4 sm:p-6">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($twoFactor->isEnabled($user))
                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900 dark:bg-emerald-950/45 dark:text-emerald-300">{{ __('security.two_factor_enabled') }}</span>
                            <a href="{{ route('two-factor.recovery-codes') }}" data-spa class="text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('profile.view_recovery_codes') }}</a>
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 dark:bg-slate-700/60 dark:text-slate-200">{{ __('security.two_factor_disabled') }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @unless ($twoFactor->isEnabled($user))
                            <a href="{{ route('two-factor.setup') }}" data-spa class="inline-flex rounded-xl bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('security.two_factor_setup') }}</a>
                        @endunless
                        <a href="{{ route('security.sessions.index') }}" data-spa class="inline-flex rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('security.sessions_title') }}</a>
                    </div>
                    @if ($twoFactor->isEnabled($user))
                        <form method="POST" action="{{ route('two-factor.disable') }}" class="max-w-md rounded-lg border border-slate-100 p-4 dark:border-[#374151]" onsubmit="return confirm(@js(__('security.two_factor_disable')));">
                            @csrf
                            @method('DELETE')
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('auth.password') }}</label>
                            <input type="password" name="password" required class="mb-3 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 dark:text-red-400">{{ __('security.two_factor_disable') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('profile.section_password') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                    <p class="m-0 text-sm text-gray-500 dark:text-[#9CA3AF]">{{ __('profile.section_password_sub') }}</p>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0 text-red-800 dark:text-red-300">{{ __('profile.danger_zone') }}</p>
            <div class="overflow-hidden rounded-xl border border-red-100 bg-white shadow-sm dark:border-red-900/45 dark:bg-[#1F2937] dark:ring-1 dark:ring-red-900/20">
                <div class="border-b border-red-50 bg-red-50/50 px-4 py-4 dark:border-red-900/40 dark:bg-red-950/35 sm:px-5">
                    <p class="m-0 text-sm text-red-800/80 dark:text-red-200/95">{{ __('profile.danger_zone_sub') }}</p>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
