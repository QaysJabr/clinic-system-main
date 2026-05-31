@php($uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true))
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="max-w-[1400px] mx-auto" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
        <div class="mb-8">
            <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('dashboard.clinical_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('dashboard.clinical_intro') }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-[#94A3B8] m-0">{{ $todayLabel }}</p>
        </div>

        @if(auth()->user()->hasRole('doctor') && ! $doctor)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="m-0 text-sm font-semibold">{{ __('dashboard.clinical_not_linked_title') }}</p>
                <p class="mt-2 m-0 text-sm opacity-90">{{ __('dashboard.clinical_not_linked_body') }}</p>
            </div>
        @endif

        @if($financial)
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                    <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('dashboard.clinical_fin_pending') }}</p>
                    <p class="mt-2 m-0 text-2xl font-bold tabular-nums text-amber-700 dark:text-amber-300">{{ number_format($financial['pending_total'], 2) }}@if(filled($cur)) <span class="text-sm font-normal text-slate-500">{{ $cur }}</span>@endif</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                    <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('dashboard.clinical_fin_paid') }}</p>
                    <p class="mt-2 m-0 text-2xl font-bold tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($financial['paid_total'], 2) }}@if(filled($cur)) <span class="text-sm font-normal text-slate-500">{{ $cur }}</span>@endif</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                    <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('dashboard.clinical_fin_records') }}</p>
                    <p class="mt-2 m-0 text-sm text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.clinical_fin_counts', ['pending' => $financial['pending_count'], 'paid' => $financial['paid_count']]) }}</p>
                    <a href="{{ route('doctor-earnings.index') }}" class="mt-2 inline-block text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-teal-300">{{ __('dashboard.clinical_view_details') }}</a>
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/90 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('dashboard.clinical_today_visits') }}</h2>
                @can('manage visits')
                    <a href="{{ route('visits.index') }}" class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-teal-300">{{ __('dashboard.clinical_all_visits') }}</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 dark:border-[#374151] dark:bg-[#111827]">
                            @if(auth()->user()->hasRole('admin'))
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.clinical_th_doctor') }}</th>
                            @endif
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.clinical_th_patient') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('dashboard.clinical_th_status') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB] w-[1%] whitespace-nowrap">{{ __('dashboard.clinical_th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayVisits as $v)
                            <tr class="border-b border-slate-100 dark:border-gray-800">
                                @if(auth()->user()->hasRole('admin'))
                                    <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ optional($v->doctor)->full_name ?? __('common.em_dash') }}</td>
                                @endif
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-[#F3F4F6]">{{ optional($v->patient)->full_name ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $v->statusBadgeClasses() }}">{{ $v->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @can('manage visits')
                                        <a href="{{ route('visits.edit', $v) }}" class="font-semibold text-[#1F7A8C] hover:underline dark:text-teal-300">{{ __('dashboard.clinical_open_edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasRole('admin') ? 4 : 3 }}" class="px-4 py-10 text-center text-slate-500 dark:text-[#9CA3AF]">{{ __('dashboard.clinical_empty_today') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
