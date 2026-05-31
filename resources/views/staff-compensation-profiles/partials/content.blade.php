@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $profileStats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'unassigned' => 0];
    $hasFilters = request()->filled('q') || request()->filled('status') || request()->filled('compensation_type');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('staff.comp_profiles_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('staff.comp_profiles_subtitle') }}</p>
        </div>
        @can('manage staff payroll')
            <a href="{{ route('staff-compensation-profiles.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('staff.comp_profiles_add') }}</a>
        @endcan
    </div>

    <x-staff.nav active="compensation" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-indigo-200/90 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-indigo-800 dark:text-indigo-300">{{ __('staff.comp_profiles_stat_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-indigo-900 dark:text-indigo-200">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('staff.status_active') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('staff.status_inactive') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ number_format($stats['inactive']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('staff.comp_profiles_stat_unassigned') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['unassigned']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('staff-compensation-profiles.index', ['status' => \App\Models\StaffCompensationProfile::STATUS_ACTIVE]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('status') === \App\Models\StaffCompensationProfile::STATUS_ACTIVE ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
            {{ __('staff.comp_profiles_quick_active') }}
        </a>
        <a href="{{ route('staff-compensation-profiles.index', ['compensation_type' => \App\Enums\StaffCompensationModel::Fixed->value]) }}" data-spa class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('compensation_type') === \App\Enums\StaffCompensationModel::Fixed->value ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('staff.comp_fixed') }}</a>
        <a href="{{ route('staff-compensation-profiles.index', ['compensation_type' => \App\Enums\StaffCompensationModel::Percentage->value]) }}" data-spa class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('compensation_type') === \App\Enums\StaffCompensationModel::Percentage->value ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('staff.comp_percentage') }}</a>
        <a href="{{ route('staff-payments.index') }}" data-spa class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300">{{ __('staff.comp_profiles_payments_log') }}</a>
        @can('manage payroll')
            <a href="{{ route('payroll-runs.index') }}" data-spa class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300">{{ __('staff.comp_profiles_payroll_periods') }}</a>
        @endcan
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('staff.section_filter') }}</p>
        <form method="GET" action="{{ route('staff-compensation-profiles.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.section_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-4">
                        <label for="comp_q" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.search_label') }}</label>
                        <input type="search" name="q" id="comp_q" value="{{ request('q') }}" placeholder="{{ __('staff.comp_profiles_search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-3">
                        <label for="comp_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profiles_col_model') }}</label>
                        <select name="compensation_type" id="comp_type"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('staff.comp_profiles_filter_all_models') }}</option>
                            @foreach (\App\Enums\StaffCompensationModel::cases() as $model)
                                <option value="{{ $model->value }}" @selected(request('compensation_type') === $model->value)>{{ $model->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="comp_status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.label_status') }}</label>
                        <select name="status" id="comp_status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('staff.filter_all_status') }}</option>
                            <option value="{{ \App\Models\StaffCompensationProfile::STATUS_ACTIVE }}" @selected(request('status') === \App\Models\StaffCompensationProfile::STATUS_ACTIVE)>{{ __('staff.status_active') }}</option>
                            <option value="{{ \App\Models\StaffCompensationProfile::STATUS_INACTIVE }}" @selected(request('status') === \App\Models\StaffCompensationProfile::STATUS_INACTIVE)>{{ __('staff.status_inactive') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-3 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('staff.btn_search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('staff-compensation-profiles.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('staff.btn_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('staff.comp_profiles_kicker') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.comp_profiles_all') }}</h2>
                    @if ($profiles->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('staff.results_count', ['from' => $profiles->firstItem(), 'to' => $profiles->lastItem(), 'total' => $profiles->total()]) }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.comp_profiles_col_staff') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.comp_profiles_col_model') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.comp_profiles_col_cycle') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.comp_profiles_col_summary') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.col_status') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.comp_profiles_col_start') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('staff.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($profiles as $profile)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('staff-compensation-profiles.edit', $profile) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ optional($profile->staff)->full_name ?? '—' }}</a>
                                    <p class="m-0 mt-0.5 text-xs text-gray-500 dark:text-[#9CA3AF]">{{ optional($profile->staff)->roleTypeLabel() ?? '' }}@if(optional($profile->staff?->user)->name) · {{ $profile->staff->user->name }}@endif</p>
                                </td>
                                <td class="px-4 py-3 sm:px-5"><x-staff.compensation-type-badge :type="$profile->compensation_type" /></td>
                                <td class="px-4 py-3 text-slate-700 dark:text-[#E5E7EB] sm:px-5">{{ $profile->payment_cycle->label() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF] sm:px-5" title="{{ $profile->summaryLabel() }}">{{ \Illuminate\Support\Str::limit($profile->summaryLabel(), 72) }}</td>
                                <td class="px-4 py-3 sm:px-5"><x-staff.compensation-status-badge :profile="$profile" /></td>
                                <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-[#9CA3AF] sm:px-5">{{ $profile->start_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-end whitespace-nowrap sm:px-5">
                                    <a href="{{ route('staff-compensation-profiles.edit', $profile) }}" data-spa class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-[#5EEAD4]">{{ __('staff.edit_link') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-slate-600 dark:text-slate-300">{{ __('staff.comp_profiles_empty_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('staff.comp_profiles_empty') }}</p>
                                    @can('manage staff payroll')
                                        <a href="{{ route('staff-compensation-profiles.create') }}" data-spa class="mt-4 inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('staff.comp_profiles_add') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($profiles->hasPages())
                <div class="border-t border-gray-100 px-4 py-4 dark:border-[#374151] sm:px-5">{{ $profiles->links() }}</div>
            @endif
        </div>
    </div>
</div>
