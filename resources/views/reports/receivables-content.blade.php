@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $receivableStats ?? ['total' => $totalReceivables ?? 0, 'count' => 0, 'unpaid' => 0, 'partial' => 0];
    $hasFilters = request()->filled('date_from') || request()->filled('date_to') || request()->filled('doctor_id');
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('invoices.receivables_page_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('invoices.receivables_subtitle', ['range' => $reportMeta['date_range_label']]) }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('reports.receivables.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-xl border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-bold text-[#0F4C81] shadow-sm hover:bg-blue-50 dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD]">{{ __('reports.nav_pdf') }}</a>
            <a href="{{ route('reports.receivables.excel', request()->query()) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-[#1F7A8C] bg-white px-4 py-2.5 text-sm font-bold text-[#1F7A8C] shadow-sm hover:bg-teal-50 dark:border-teal-500/50 dark:bg-[#111827] dark:text-teal-300">{{ __('reports.nav_excel') }}</a>
        </div>
    </div>

    <x-reports.nav active="receivables" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-rose-200/90 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-rose-800 dark:text-rose-300">{{ __('reports.receivables_stat_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-rose-900 dark:text-rose-200">
                {{ number_format($stats['total'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('reports.receivables_stat_invoices') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['count']) }}</p>
        </div>
        <div class="rounded-xl border border-red-200/90 bg-red-50/50 p-4 shadow-sm dark:border-red-900/40 dark:bg-red-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-red-800 dark:text-red-300">{{ __('reports.receivables_stat_unpaid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-red-900 dark:text-red-200">{{ number_format($stats['unpaid']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('reports.receivables_stat_partial') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['partial']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('reports.section_filter') }}</p>
        <form method="get" action="{{ route('reports.receivables') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('reports.receivables_filter_heading') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-3">
                        <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_date_from') }}</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_date_to') }}</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="lg:col-span-4">
                        <label for="doctor_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_doctor') }}</label>
                        <select name="doctor_id" id="doctor_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('reports.filter_all') }}</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}" @selected((string) request('doctor_id') === (string) $d->id)>{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:col-span-2">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('invoices.receivables_apply') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('reports.receivables') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-[#374151] dark:bg-[#111827] dark:text-slate-300">{{ __('reports.btn_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="dash-section-label m-0">{{ __('reports.section_receivables_list') }}</p>
            @if ($invoices->total() > 0)
                <p class="m-0 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('invoices.results_count', ['from' => $invoices->firstItem(), 'to' => $invoices->lastItem(), 'total' => $invoices->total()]) }}
                </p>
            @endif
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                <tr>
                    <th class="px-4 py-3">{{ __('invoices.field_patient') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_invoice_number') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_doctor') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_total') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_paid') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_remaining') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_status') }}</th>
                    <th class="px-4 py-3">{{ __('invoices.field_due_date_column') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                @forelse($invoices as $inv)
                    @php($rem = round((float) $inv->total - (float) $inv->paid, 2))
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-[#111827]/50">
                        <td class="px-4 py-3 font-medium">{{ $inv->patient->full_name ?? __('common.em_dash') }}</td>
                        <td class="px-4 py-3 tabular-nums">
                            <a href="{{ route('invoices.show', $inv) }}" data-no-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-4 py-3">
                            @if ($inv->treatingDoctor)
                                <a href="{{ route('reports.doctors.show', $inv->treatingDoctor) }}" data-spa class="text-slate-800 hover:text-[#0F4C81] dark:text-slate-200 dark:hover:text-[#93C5FD]">{{ $inv->treatingDoctor->full_name }}</a>
                            @else
                                {{ __('common.em_dash') }}
                            @endif
                        </td>
                        <td class="px-4 py-3 tabular-nums">{{ number_format((float) $inv->total, 2) }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ number_format((float) $inv->paid, 2) }}</td>
                        <td class="px-4 py-3 tabular-nums font-semibold text-rose-700 dark:text-rose-300">{{ number_format($rem, 2) }}</td>
                        <td class="px-4 py-3"><x-invoice.status-badge :status="$inv->status" /></td>
                        <td class="px-4 py-3 tabular-nums">{{ $inv->due_date?->format('d/m/Y') ?? __('common.em_dash') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <p class="m-0 text-base font-semibold text-slate-700 dark:text-slate-200">{{ __('invoices.receivables_empty') }}</p>
                            <p class="m-0 mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('reports.receivables_empty_hint') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="mt-4">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
