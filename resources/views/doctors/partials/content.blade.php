@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $doctorDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $stats = $doctorStats ?? ['total' => 0, 'active' => 0, 'inactive' => 0];
    $hasFilters = request()->filled('search') || request()->filled('status');
@endphp
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $doctorDir }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('doctors.heading_manage') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('doctors.subtitle_index') }}</p>
        </div>
        <a href="{{ route('doctors.onboarding.create') }}" data-no-spa class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('doctors.add_doctor') }}</a>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('doctors.stat_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-teal-200/90 bg-teal-50/50 p-4 shadow-sm dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-teal-800 dark:text-teal-300">{{ __('doctors.stat_active') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-slate-50/80 p-4 shadow-sm dark:border-[#374151] dark:bg-[#111827]/60">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('doctors.stat_inactive') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($stats['inactive']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('doctors.section_filter') }}</p>
        <form method="GET" action="{{ route('doctors.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.section_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-5">
                        <label for="search" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.search_label') }}</label>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('doctors.search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.field_status') }}</label>
                        <select name="status" id="status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('doctors.filter_all') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('common.inactive') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:col-span-4 lg:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('doctors.search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('doctors.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('doctors.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('doctors.section_list') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.section_all_doctors') }}</h2>
                    @if ($doctors->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('doctors.results_count', [
                                'from' => $doctors->firstItem(),
                                'to' => $doctors->lastItem(),
                                'total' => $doctors->total(),
                            ]) }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.col_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.col_specialty') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.col_phone') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.col_room') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.col_status') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('doctors.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('doctors.edit', $doctor) }}" data-no-spa class="group flex min-w-0 items-center gap-3">
                                        <x-doctor.avatar :name="$doctor->full_name" size="sm" />
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-gray-900 transition group-hover:text-[#0F4C81] dark:text-[#F3F4F6] dark:group-hover:text-[#93C5FD]">{{ $doctor->full_name }}</span>
                                            @if (filled($doctor->email))
                                                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ $doctor->email }}</span>
                                            @endif
                                        </span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $doctor->specialty ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $doctor->phone ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $doctor->room_number ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $doctor->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-emerald-950/45 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300' }}">
                                        {{ $doctor->status === 'active' ? __('common.active') : __('common.inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <a href="{{ route('doctors.edit', $doctor) }}" data-no-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('doctors.edit') }}</a>
                                        <form method="POST" action="{{ route('doctors.destroy', $doctor) }}" class="inline" data-confirm-title="{{ __('doctors.confirm_delete_title') }}" data-confirm="{{ __('doctors.confirm_delete_body') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-red-600 hover:underline dark:text-red-400">{{ __('doctors.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-gray-700 dark:text-gray-200">{{ __('doctors.empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $hasFilters ? __('doctors.empty_filtered') : __('doctors.empty_hint') }}</p>
                                    @if (! $hasFilters)
                                        <a href="{{ route('doctors.onboarding.create') }}" data-no-spa class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('doctors.add_doctor') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($doctors->hasPages())
                <div class="border-t border-gray-100 bg-[#FAFBFC] px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/40 sm:px-5">
                    {{ $doctors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
