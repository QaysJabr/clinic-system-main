@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $userStats ?? ['total' => 0, 'admins' => 0, 'doctors' => 0, 'staff' => 0];
    $hasFilters = request()->filled('search') || request()->filled('role');
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('settings.users_heading') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('settings.users_intro') }}</p>
        </div>
        @can('manage users')
            <a href="{{ route('users.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('settings.users_btn_add') }}</a>
        @endcan
    </div>

    <x-settings.nav active="users" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('settings.users_stat_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('settings.users_stat_admins') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['admins']) }}</p>
        </div>
        <div class="rounded-xl border border-cyan-200/90 bg-cyan-50/50 p-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-cyan-900 dark:text-cyan-300">{{ __('settings.users_stat_doctors') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-cyan-900 dark:text-cyan-200">{{ number_format($stats['doctors']) }}</p>
        </div>
        <div class="rounded-xl border border-teal-200/90 bg-teal-50/50 p-4 shadow-sm dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-teal-900 dark:text-teal-300">{{ __('settings.users_stat_staff') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-teal-900 dark:text-teal-200">{{ number_format($stats['staff']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('settings.users_section_filter') }}</p>
        <form method="GET" action="{{ route('users.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('settings.users_filter_heading') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-5">
                        <label for="search" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('settings.users_filter_search') }}</label>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('settings.users_filter_search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="role" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('settings.users_th_role') }}</label>
                        <select name="role" id="role"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('settings.users_filter_all_roles') }}</option>
                            @foreach ($roleOptions ?? [] as $roleName)
                                <option value="{{ $roleName }}" @selected(request('role') === $roleName)>
                                    @switch($roleName)
                                        @case('admin') {{ __('chat.role_admin') }} @break
                                        @case('clinic_owner') {{ __('settings.users_role_clinic_owner') }} @break
                                        @case('doctor') {{ __('chat.role_doctor') }} @break
                                        @case('receptionist') {{ __('chat.role_receptionist') }} @break
                                        @case('accountant') {{ __('chat.role_accountant') }} @break
                                        @default {{ $roleName }}
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:col-span-4 lg:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('settings.users_filter_apply') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('users.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('settings.users_filter_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('settings.users_section_list') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('settings.users_panel_title') }}</h2>
                    @if ($users->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('settings.users_results_count', [
                                'from' => $users->firstItem(),
                                'to' => $users->lastItem(),
                                'total' => $users->total(),
                            ]) }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('settings.users_th_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('settings.users_th_email') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('settings.users_th_role') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('settings.users_th_created') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('settings.users_th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $u->name }}</span>
                                    @if ($u->id === auth()->id())
                                        <span class="ms-2 inline-flex rounded-full bg-[#0F4C81]/10 px-2 py-0.5 text-xs font-semibold text-[#0F4C81] dark:bg-[#3B82F6]/20 dark:text-[#93C5FD]">{{ __('settings.users_you_badge') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $u->email }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($u->roles as $role)
                                            <x-user.role-badge :role-name="$role->name" />
                                        @empty
                                            <span class="text-gray-400 dark:text-[#94A3B8]">{{ __('common.em_dash') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">@safeDate($u->created_at)</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @can('manage users')
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <a href="{{ route('users.edit', $u) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('settings.users_action_edit') }}</a>
                                            @if($u->id !== auth()->id())
                                                <span class="text-gray-300 dark:text-[#4B5563]" aria-hidden="true">·</span>
                                                <form action="{{ route('users.destroy', $u) }}" method="POST" class="m-0 inline" data-confirm-title="{{ e(__('settings.users_confirm_delete_title')) }}" data-confirm="{{ e(__('settings.users_confirm_delete', ['name' => $u->name, 'email' => $u->email])) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="m-0 cursor-pointer border-0 bg-transparent p-0 font-semibold text-red-600 hover:underline dark:text-red-400">{{ __('settings.users_action_delete') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-gray-700 dark:text-gray-200">{{ __('settings.users_empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $hasFilters ? __('settings.users_empty_filtered') : __('settings.users_empty') }}</p>
                                    @can('manage users')
                                        @if (! $hasFilters)
                                            <a href="{{ route('users.create') }}" data-spa class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white dark:bg-[#3B82F6]">{{ __('settings.users_btn_add') }}</a>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-gray-100 bg-[#FAFBFC] px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/40 sm:px-5">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
