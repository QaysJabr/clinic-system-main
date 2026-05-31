@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $clinic = \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $stats = $purchaseStats ?? ['month_count' => 0, 'month_total' => 0, 'with_expense' => 0, 'all' => 0];
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.purchases_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('inventory.purchases_subtitle') }}</p>
        </div>
        @can('manage inventory')
            <a href="{{ route('inventory.purchases.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md dark:bg-[#3B82F6]">{{ __('inventory.btn_receive_stock') }}</a>
        @endcan
    </div>

    <x-inventory.nav />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-teal-200/90 bg-teal-50/50 p-4 shadow-sm dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-teal-800 dark:text-teal-300">{{ __('inventory.stat_purchases_month') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-teal-900 dark:text-teal-200">{{ number_format($stats['month_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('inventory.stat_purchases_month_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">
                {{ number_format($stats['month_total'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('inventory.stat_linked_expenses') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['with_expense']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('inventory.stat_purchases_all') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ number_format($stats['all']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.list_heading_purchases') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                @if ($purchases->total() > 0)
                    <p class="m-0 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('inventory.results_count', ['from' => $purchases->firstItem(), 'to' => $purchases->lastItem(), 'total' => $purchases->total()]) }}
                    </p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-[#374151]">
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_purchase_date') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_reference') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_supplier') }}</th>
                            <th class="px-4 py-3 text-end font-bold">{{ __('inventory.th_total') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.th_finance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $p)
                            <tr class="border-b border-gray-50 hover:bg-slate-50/80 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $p->purchase_date?->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $p->documentNumber() }}</td>
                                <td class="px-4 py-3 font-medium">{{ $p->supplier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-end tabular-nums font-bold text-[#0F4C81] dark:text-[#93C5FD]">
                                    {{ number_format((float) $p->total_amount, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-slate-500">{{ $cur }}</span>@endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($p->expense_id && auth()->user()->can('manage expenses'))
                                        <a href="{{ route('expenses.edit', $p->expense_id) }}" data-spa class="inline-flex items-center gap-1 text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('inventory.link_expense') }}</a>
                                    @elseif ((float) $p->total_amount > 0)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ __('inventory.purchase_no_expense') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-14 text-center">
                                    <p class="m-0 font-semibold text-slate-600 dark:text-slate-300">{{ __('inventory.empty_purchases_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500">{{ __('inventory.empty_purchases') }}</p>
                                    @can('manage inventory')
                                        <a href="{{ route('inventory.purchases.create') }}" data-spa class="mt-4 inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('inventory.btn_receive_stock') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($purchases->hasPages())
                <div class="border-t px-4 py-3 dark:border-[#374151]">{{ $purchases->links() }}</div>
            @endif
        </div>
    </div>
</div>
