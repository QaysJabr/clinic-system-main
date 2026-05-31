@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $itemStats ?? ['total' => 0, 'low_stock' => 0, 'expiring' => 0, 'inactive' => 0];
    $hasFilters = request()->filled('q') || request()->filled('inventory_category_id') || request()->filled('status') || request()->boolean('low_stock');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.items_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('inventory.items_subtitle') }}</p>
        </div>
        @can('manage inventory')
            <a href="{{ route('inventory.items.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('inventory.btn_add_item') }}</a>
        @endcan
    </div>

    <x-inventory.nav />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('inventory.stat_active_items') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('inventory.kpi_low_stock') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['low_stock']) }}</p>
        </div>
        <div class="rounded-xl border border-rose-200/90 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-rose-800 dark:text-rose-300">{{ __('inventory.kpi_expiring') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-rose-900 dark:text-rose-200">{{ number_format($stats['expiring']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('inventory.stat_inactive_items') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ number_format($stats['inactive']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('inventory.items.index', ['low_stock' => 1]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request()->boolean('low_stock') ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
            {{ __('inventory.quick_low_stock') }}
        </a>
        <a href="{{ route('inventory.items.index', ['status' => \App\Support\Inventory\InventoryItemStatus::ACTIVE]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('status') === \App\Support\Inventory\InventoryItemStatus::ACTIVE ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
            {{ __('inventory.quick_active_only') }}
        </a>
        <a href="{{ route('inventory.reports.index') }}" data-spa class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300">{{ __('inventory.nav_reports') }}</a>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.filter_results') }}</p>
        <form method="GET" action="{{ route('inventory.items.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('inventory.filter_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-4">
                        <label for="inv_q" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.search') }}</label>
                        <input type="search" name="q" id="inv_q" value="{{ request('q') }}" placeholder="{{ __('inventory.search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-3">
                        <label for="inventory_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_category') }}</label>
                        <select name="inventory_category_id" id="inventory_category_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('inventory.filter_all') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) request('inventory_category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label for="inv_status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_status') }}</label>
                        <select name="status" id="inv_status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('inventory.filter_all') }}</option>
                            @foreach ($statusLabels as $code => $label)
                                <option value="{{ $code }}" @selected(request('status') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center xl:col-span-2 xl:pb-2">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">
                            <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock')) class="rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]/20 dark:border-[#4B5563] dark:bg-[#111827]">
                            {{ __('inventory.filter_low_stock') }}
                        </label>
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-12 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('inventory.items.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.list_heading_items') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('inventory.table_heading_items') }}</h2>
                    @if ($items->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('inventory.results_count', ['from' => $items->firstItem(), 'to' => $items->lastItem(), 'total' => $items->total()]) }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_sku') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_category') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_status') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.th_stock') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="border-b border-gray-50 transition hover:bg-slate-50/80 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $item->name }}</div>
                                    @if ($item->isExpiringSoon())
                                        <p class="m-0 mt-0.5 text-xs text-rose-600 dark:text-rose-400">{{ __('inventory.expires_on', ['date' => $item->expiry_date->format('Y-m-d')]) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $item->sku ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $item->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><x-inventory.status-badge :item="$item" /></td>
                                <td class="px-4 py-3 text-end">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold tabular-nums {{ $item->stockBadgeClass() }}">
                                        {{ number_format((float) $item->quantity_on_hand, 3) }}
                                        @if ($item->unit)
                                            <span class="ms-1 font-normal opacity-80">{{ $item->unit->name }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end whitespace-nowrap">
                                    @can('manage inventory')
                                        <a href="{{ route('inventory.items.edit', $item) }}" data-spa class="text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('common.edit') }}</a>
                                        <span class="mx-2 text-slate-300 dark:text-slate-600" aria-hidden="true">|</span>
                                        <a href="{{ route('inventory.movements.index', ['inventory_item_id' => $item->id]) }}" data-spa class="text-sm font-semibold text-slate-600 hover:underline dark:text-slate-400">{{ __('inventory.link_history') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <p class="m-0 text-base font-semibold text-slate-600 dark:text-slate-300">{{ __('inventory.empty_items_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('inventory.empty_items') }}</p>
                                    @can('manage inventory')
                                        <a href="{{ route('inventory.items.create') }}" data-spa class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('inventory.btn_add_item') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-[#374151]">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</div>
