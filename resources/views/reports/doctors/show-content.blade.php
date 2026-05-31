@php
    $uiRtl = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $invoiceCount = $invoices->count();
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <a href="{{ route('reports.index') }}" data-spa class="mb-2 inline-flex items-center gap-1 text-sm font-semibold text-slate-600 hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">
                <span aria-hidden="true">←</span>
                {{ __('reports.back_to_financial') }}
            </a>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('doctors.report_heading') }}</h1>
            <p class="m-0 mt-1 text-lg font-semibold text-slate-800 dark:text-[#F3F4F6]">{{ $doctor->full_name }}</p>
            <p class="m-0 mt-2 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ $reportMeta['date_range_label'] }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('reports.doctors.pdf', array_merge(['doctor' => $doctor->id], request()->query())) }}" class="inline-flex items-center justify-center rounded-xl border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-bold text-[#0F4C81] shadow-sm dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD]">{{ __('reports.nav_pdf') }}</a>
            <a href="{{ route('reports.doctors.excel', array_merge(['doctor' => $doctor->id], request()->query())) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-[#1F7A8C] bg-white px-4 py-2.5 text-sm font-bold text-[#1F7A8C] shadow-sm dark:border-teal-500/50 dark:bg-[#111827] dark:text-teal-300">{{ __('reports.nav_excel') }}</a>
        </div>
    </div>

    <x-reports.nav active="doctor" :doctor="$doctor" />

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('reports.section_filter') }}</p>
        <form method="get" action="{{ route('reports.doctors.show', $doctor) }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-4">
                        <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.report_date_from') }}</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="lg:col-span-4">
                        <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.report_date_to') }}</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="flex flex-wrap gap-2 lg:col-span-4">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('doctors.apply') }}</button>
                        @if (request()->filled('date_from') || request()->filled('date_to'))
                            <a href="{{ route('reports.doctors.show', $doctor) }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-[#374151] dark:bg-[#111827] dark:text-slate-300">{{ __('reports.btn_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm border-t-4 border-t-slate-500 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('doctors.stat_invoice_count') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-900 dark:text-slate-100">{{ $stats['invoice_count'] }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm border-t-4 border-t-emerald-500 dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('doctors.stat_accrual_revenue') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-800 dark:text-emerald-300">
                {{ number_format($stats['accrual_revenue'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-cyan-200/90 bg-cyan-50/50 p-4 shadow-sm border-t-4 border-t-cyan-500 dark:border-cyan-900/40 dark:bg-cyan-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-cyan-900 dark:text-cyan-300">{{ __('doctors.stat_doctor_share_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-cyan-900 dark:text-cyan-300">
                {{ number_format($stats['doctor_share_total'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm border-t-4 border-t-amber-500 dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">{{ __('doctors.stat_pending') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">
                {{ number_format($stats['earnings_pending'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
            <p class="m-0 mt-1 text-xs text-amber-800/80 dark:text-amber-300/80">{{ __('doctors.stat_pending_records', ['count' => $stats['earnings_pending_count']]) }}</p>
        </div>
        <div class="rounded-xl border border-teal-200/90 bg-teal-50/50 p-4 shadow-sm border-t-4 border-t-teal-500 dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-teal-900 dark:text-teal-300">{{ __('doctors.stat_paid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-teal-900 dark:text-teal-200">
                {{ number_format($stats['earnings_paid'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
            <p class="m-0 mt-1 text-xs text-teal-800/80 dark:text-teal-300/80">{{ __('doctors.stat_paid_records', ['count' => $stats['earnings_paid_count']]) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="dash-section-label m-0">{{ __('reports.doctor_invoices_section') }}</p>
            @if ($invoiceCount > 0)
                <p class="m-0 text-sm text-slate-500 dark:text-slate-400">{{ __('reports.doctor_invoices_count', ['count' => $invoiceCount]) }}</p>
            @endif
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                <tr>
                    <th class="px-4 py-3">{{ __('doctors.report_th_date') }}</th>
                    <th class="px-4 py-3">{{ __('doctors.report_th_patient') }}</th>
                    <th class="px-4 py-3">{{ __('doctors.report_th_invoice') }}</th>
                    <th class="px-4 py-3">{{ __('doctors.report_th_total') }}</th>
                    <th class="px-4 py-3">{{ __('doctors.report_th_paid') }}</th>
                    <th class="px-4 py-3">{{ __('doctors.report_th_doctor_share') }}</th>
                    <th class="px-4 py-3">{{ __('doctors.report_th_settlement') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                @forelse($invoices as $inv)
                    @php($earning = $inv->doctorEarnings->first())
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-[#111827]/50">
                        <td class="px-4 py-3 tabular-nums">{{ $inv->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $inv->patient->full_name ?? __('common.em_dash') }}</td>
                        <td class="px-4 py-3 tabular-nums">
                            <a href="{{ route('invoices.show', $inv) }}" data-no-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-4 py-3 tabular-nums">{{ number_format((float) $inv->total, 2) }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ number_format((float) $inv->paid, 2) }}</td>
                        <td class="px-4 py-3 tabular-nums font-semibold">{{ $earning ? number_format((float) $earning->earning_amount, 2) : __('common.em_dash') }}</td>
                        <td class="px-4 py-3">
                            @if ($earning)
                                <x-doctor-earning.status-badge :earning="$earning" />
                            @else
                                {{ __('common.em_dash') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">{{ __('reports.doctor_invoices_empty') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
