@php
    $clinic = \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $earningStats ?? ['pending_total' => 0, 'paid_total' => 0, 'pending_count' => 0, 'records_count' => 0];
    $hasFilters = filled($filterDoctorId ?? null) || filled($filterStatus ?? null) || filled($filterDateFrom ?? null) || filled($filterDateTo ?? null);
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd = now()->endOfMonth()->toDateString();
    $isOwnView = auth()->user()->hasRole('doctor') && ! auth()->user()->hasRole('admin');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ $isOwnView ? __('doctors.earnings_page_title_own') : __('doctors.earnings_page_title_all') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ $isOwnView ? __('doctors.earnings_subtitle_own') : __('doctors.earnings_subtitle_all') }}</p>
        </div>
        <a href="{{ route('doctor-earnings.export', request()->query()) }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#F3F4F6]">{{ __('doctors.earnings_export_csv') }}</a>
    </div>

    <x-doctor-earnings.nav active="ledger" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('doctors.earnings_total_pending') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">
                {{ number_format($stats['pending_total'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('doctors.earnings_total_marked_paid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">
                {{ number_format($stats['paid_total'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('doctors.earnings_stat_pending_count') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['pending_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('doctors.earnings_last_settlement') }}</p>
            <p class="m-0 mt-1 text-lg font-semibold tabular-nums text-slate-800 dark:text-slate-200">
                @if ($lastPaymentDate ?? null)
                    {{ \Illuminate\Support\Carbon::parse($lastPaymentDate)->format('d/m/Y H:i') }}
                @else
                    {{ __('common.em_dash') }}
                @endif
            </p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('doctor-earnings.index', ['status' => \App\Models\DoctorEarning::STATUS_PENDING]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ ($filterStatus ?? '') === \App\Models\DoctorEarning::STATUS_PENDING ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
            {{ __('doctors.earnings_status_pending') }}
        </a>
        <a href="{{ route('doctor-earnings.index', ['status' => \App\Models\DoctorEarning::STATUS_PAID]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ ($filterStatus ?? '') === \App\Models\DoctorEarning::STATUS_PAID ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
            {{ __('doctors.earnings_status_paid_option') }}
        </a>
        <a href="{{ route('doctor-earnings.index', ['date_from' => $monthStart, 'date_to' => $monthEnd]) }}" data-spa class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ ($filterDateFrom ?? '') === $monthStart && ($filterDateTo ?? '') === $monthEnd ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('doctors.earnings_quick_this_month') }}</a>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('doctors.earnings_filter_heading') }}</p>
        <form method="GET" action="{{ route('doctor-earnings.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.earnings_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    @if ($doctorFilterList->isNotEmpty())
                        <div class="xl:col-span-3">
                            <label for="doctor_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_label_doctor') }}</label>
                            <select name="doctor_id" id="doctor_id"
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                <option value="">{{ __('doctors.earnings_filter_all') }}</option>
                                @foreach ($doctorFilterList as $d)
                                    <option value="{{ $d->id }}" @selected((string) ($filterDoctorId ?? '') === (string) $d->id)>{{ $d->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="xl:col-span-3">
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_label_status') }}</label>
                        <select name="status" id="status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('doctors.earnings_filter_all') }}</option>
                            <option value="{{ \App\Models\DoctorEarning::STATUS_PENDING }}" @selected(($filterStatus ?? '') === \App\Models\DoctorEarning::STATUS_PENDING)>{{ __('doctors.earnings_status_pending') }}</option>
                            <option value="{{ \App\Models\DoctorEarning::STATUS_PAID }}" @selected(($filterStatus ?? '') === \App\Models\DoctorEarning::STATUS_PAID)>{{ __('doctors.earnings_status_paid_option') }}</option>
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_label_date_from') }}</label>
                        <input type="date" name="date_from" id="date_from" value="{{ $filterDateFrom ?? '' }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_label_date_to') }}</label>
                        <input type="date" name="date_to" id="date_to" value="{{ $filterDateTo ?? '' }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-4 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('doctors.earnings_search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('doctor-earnings.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('doctors.earnings_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    @can('manage doctor earnings')
        @if (isset($batchPayDoctors) && $batchPayDoctors->isNotEmpty())
            <div class="dash-section-group mb-6">
                <p class="dash-section-label m-0">{{ __('doctors.earnings_batch_title') }}</p>
                <div class="overflow-hidden rounded-xl border border-teal-200/90 bg-teal-50/30 p-4 shadow-sm dark:border-teal-900/40 dark:bg-teal-950/20 sm:p-5">
                    <p class="m-0 text-xs text-slate-600 dark:text-slate-400">{{ __('doctors.earnings_batch_hint') }}</p>
                    <form method="POST" action="{{ route('doctor-earnings.batch-pay') }}" class="mt-4 flex flex-wrap items-end gap-3" data-confirm-title="{{ __('doctors.earnings_batch_confirm_title') }}" data-confirm="{{ __('doctors.earnings_batch_confirm_body') }}">
                        @csrf
                        @if ($batchPayDoctors->count() === 1)
                            <input type="hidden" name="doctor_id" value="{{ $batchPayDoctors->first()->id }}">
                            <p class="m-0 text-sm text-gray-800 dark:text-[#E5E7EB]">{{ __('doctors.earnings_batch_doctor_label') }} <span class="font-semibold">{{ $batchPayDoctors->first()->full_name }}</span></p>
                        @else
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_label_doctor') }}</label>
                                <select name="doctor_id" required class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                    @foreach ($batchPayDoctors as $d)
                                        <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_batch_date_from') }}</label>
                            <input type="date" name="date_from" value="{{ $filterDateFrom ?? '' }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('doctors.earnings_batch_date_to') }}</label>
                            <input type="date" name="date_to" value="{{ $filterDateTo ?? '' }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                        </div>
                        <button type="submit" class="rounded-lg bg-[#1F7A8C] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#186770] dark:bg-teal-700 dark:hover:bg-teal-600">{{ __('doctors.earnings_batch_submit') }}</button>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('doctors.earnings_ledger_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.earnings_table_title') }}</h2>
                @if ($earnings->total() > 0)
                    <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('staff.results_count', ['from' => $earnings->firstItem(), 'to' => $earnings->lastItem(), 'total' => $earnings->total()]) }}
                    </p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_doctor_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_ref') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_base_amount') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_rate') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_earning') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_status') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('doctors.earnings_th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($earnings as $row)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6] sm:px-5">
                                    {{ optional(optional($row->doctor)->staff)->full_name ?? optional($row->doctor)->full_name ?? __('common.em_dash') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 sm:px-5">
                                    @if ($row->invoice_id)
                                        <a href="{{ route('invoices.edit', $row->invoice_id) }}" data-spa class="block text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('doctors.earnings_invoice_prefix') }} {{ optional($row->invoice)->invoice_number ?? '#'.$row->invoice_id }}</a>
                                    @endif
                                    @if ($row->visit_id)
                                        <a href="{{ route('visits.show', $row->visit_id) }}" data-spa class="mt-0.5 block text-xs text-slate-600 hover:underline dark:text-slate-400">{{ __('doctors.earnings_visit_prefix', ['id' => $row->visit_id]) }}@if(optional($row->visit)->visit_date) — {{ optional($row->visit)->visit_date->format('d/m/Y') }}@endif</a>
                                    @endif
                                    @if (! $row->invoice_id && ! $row->visit_id)
                                        {{ __('common.em_dash') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-[#E5E7EB] sm:px-5">{{ number_format((float) $row->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-end tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ number_format((float) $row->percentage_rate, 2) }}%</td>
                                <td class="px-4 py-3 text-end tabular-nums font-semibold text-[#0F4C81] dark:text-[#93C5FD] sm:px-5">{{ number_format((float) $row->earning_amount, 2) }}</td>
                                <td class="px-4 py-3 sm:px-5"><x-doctor-earning.status-badge :earning="$row" /></td>
                                <td class="px-4 py-3 text-end whitespace-nowrap sm:px-5">
                                    @can('manage doctor earnings')
                                        @if ($row->status === \App\Models\DoctorEarning::STATUS_PENDING)
                                            <form method="POST" action="{{ route('doctor-earnings.pay', $row) }}" class="inline">
                                                @csrf
                                                @foreach (request()->only(['doctor_id', 'status', 'date_from', 'date_to']) as $k => $v)
                                                    @if ($v !== null && $v !== '')
                                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-[#5EEAD4]">{{ __('doctors.earnings_mark_paid') }}</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">{{ $row->paid_at?->format('d/m/Y') ?? __('common.em_dash') }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">{{ __('common.em_dash') }}</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-slate-600 dark:text-slate-300">{{ __('doctors.earnings_empty_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('doctors.earnings_empty') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($earnings->hasPages())
                <div class="border-t border-gray-100 px-4 py-4 dark:border-[#374151] sm:px-5">{{ $earnings->links() }}</div>
            @endif
        </div>
    </div>
</div>
