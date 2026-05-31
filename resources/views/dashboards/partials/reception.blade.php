@php($uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true))
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="max-w-[1400px] mx-auto" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('dashboard.reception_title') }}</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('dashboard.reception_intro') }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-[#94A3B8] m-0">{{ $todayLabel }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('patients.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('dashboard.reception_new_patient') }}</a>
                <a href="{{ route('visits.create') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#F3F4F6] dark:hover:bg-[#374151]">{{ __('dashboard.reception_new_visit') }}</a>
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#F3F4F6] dark:hover:bg-[#374151]">{{ __('invoices.nav') }}</a>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-sm font-medium text-slate-600 dark:text-[#9CA3AF]">{{ __('dashboard.reception_stat_queue') }}</p>
                <p class="mt-2 m-0 text-3xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ $waitingQueue->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-sm font-medium text-slate-600 dark:text-[#9CA3AF]">{{ __('dashboard.reception_stat_open_invoices') }}</p>
                <p class="mt-2 m-0 text-3xl font-bold tabular-nums text-amber-700 dark:text-amber-300">{{ $openInvoicesCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-sm font-medium text-slate-600 dark:text-[#9CA3AF]">{{ __('dashboard.reception_find_patient') }}</p>
                <form action="{{ route('patients.index') }}" method="GET" class="mt-3 flex gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('dashboard.reception_search_placeholder') }}"
                        class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]" />
                    <button type="submit" class="shrink-0 rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-900 dark:bg-slate-600">{{ __('dashboard.reception_search_submit') }}</button>
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/90 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('dashboard.reception_queue_heading') }}</h2>
                <a href="{{ route('patients.index') }}" class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-teal-300">{{ __('dashboard.reception_all_patients') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.reception_th_patient') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.reception_th_doctor') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.reception_th_status') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB] w-[1%] whitespace-nowrap">{{ __('dashboard.reception_th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waitingQueue as $v)
                            <tr class="border-b border-slate-100 dark:border-gray-800">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-900 dark:text-[#F3F4F6]">{{ optional($v->patient)->full_name ?? __('common.em_dash') }}</span>
                                    @if(optional($v->patient)->file_number)
                                        <span class="block text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('dashboard.reception_file_label', ['number' => $v->patient->file_number]) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ optional($v->doctor)->full_name ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $v->statusBadgeClasses() }}">{{ $v->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @can('manage visits')
                                        <a href="{{ route('visits.edit', $v) }}" class="font-semibold text-[#1F7A8C] hover:underline dark:text-teal-300">{{ __('dashboard.reception_open_visit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-slate-500 dark:text-[#9CA3AF]">{{ __('dashboard.reception_empty_queue') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('dashboard.reception_recent_heading') }}</h2>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-[#374151]">
                @foreach($recentPatients as $p)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 sm:px-5">
                        <span class="font-medium text-slate-900 dark:text-[#F3F4F6]">{{ $p->full_name }}</span>
                        <span class="text-xs text-slate-500 dark:text-[#94A3B8]">{{ $p->phone ?? __('common.em_dash') }}</span>
                        <a href="{{ route('patients.show', $p) }}" class="text-sm font-semibold text-[#1F7A8C] hover:underline">{{ __('dashboard.reception_profile_link') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
