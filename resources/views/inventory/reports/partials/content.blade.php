@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $hasFilters = request()->filled('type');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.reports_title') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('inventory.reports_subtitle') }}</p>
    </div>

    <x-inventory.nav />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('inventory.kpi_valuation') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">{{ number_format($stats['valuation'] ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('inventory.kpi_low_stock') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ $lowStock->count() }}</p>
        </div>
        <div class="rounded-xl border border-rose-200/90 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-rose-800 dark:text-rose-300">{{ __('inventory.kpi_expiring') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-rose-900 dark:text-rose-200">{{ $expiring->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('inventory.section_recent_movements') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ $movements->count() }}</p>
        </div>
    </div>

    <div class="dash-section-group mb-6">
        <p class="dash-section-label m-0">{{ __('inventory.filter_results') }}</p>
        <form method="GET" action="{{ route('inventory.reports.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
                <div class="min-w-[200px] flex-1">
                    <label for="report_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.filter_movement_type') }}</label>
                    <select name="type" id="report_type" class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                        <option value="">{{ __('inventory.filter_all') }}</option>
                        @foreach ($typeLabels as $code => $label)
                            <option value="{{ $code }}" @selected(request('type') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white dark:bg-[#3B82F6]">{{ __('common.search') }}</button>
                @if ($hasFilters)
                    <a href="{{ route('inventory.reports.index') }}" data-spa class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('common.reset') }}</a>
                @endif
            </div>
        </form>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div class="dash-section-group m-0">
            <p class="dash-section-label m-0">{{ __('inventory.section_low_stock') }}</p>
            <div class="overflow-hidden rounded-xl border border-amber-200/90 bg-white shadow-sm dark:border-amber-900/40 dark:bg-[#1F2937]">
                <ul class="m-0 list-none divide-y divide-gray-100 p-0 dark:divide-[#374151]">
                    @forelse ($lowStock as $item)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <a href="{{ route('inventory.items.edit', $item) }}" data-spa class="font-medium text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ $item->name }}</a>
                            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold tabular-nums text-amber-900 dark:bg-amber-950/50 dark:text-amber-300">{{ number_format((float) $item->quantity_on_hand, 3) }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-slate-500">{{ __('inventory.report_none_low') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="dash-section-group m-0">
            <p class="dash-section-label m-0">{{ __('inventory.section_expiring') }}</p>
            <div class="overflow-hidden rounded-xl border border-rose-200/90 bg-white shadow-sm dark:border-rose-900/40 dark:bg-[#1F2937]">
                <ul class="m-0 list-none divide-y divide-gray-100 p-0 dark:divide-[#374151]">
                    @forelse ($expiring as $item)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <span class="font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $item->name }}</span>
                            <span class="shrink-0 text-xs font-semibold text-rose-700 dark:text-rose-300">{{ $item->expiry_date?->format('Y-m-d') }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-slate-500">{{ __('inventory.report_none_expiring') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.movements_title') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90">
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('common.type') }}</th>
                            <th class="px-4 py-3 text-end font-bold">{{ __('inventory.field_quantity') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('common.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $m)
                            <tr class="border-b border-gray-50 dark:border-[#374151]/60">
                                <td class="px-4 py-3">{{ $m->item?->name }}</td>
                                <td class="px-4 py-3"><x-inventory.movement-type-badge :type="$m->type" :label="$typeLabels[$m->type] ?? $m->type" /></td>
                                <td class="px-4 py-3 text-end tabular-nums font-semibold">{{ number_format((float) $m->quantity, 3) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $m->movement_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">{{ __('inventory.empty_movements') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
