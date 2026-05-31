@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $movementStats ?? ['today' => 0, 'inbound' => 0, 'outbound' => 0, 'consumption' => 0];
    $hasFilters = request()->filled('inventory_item_id') || request()->filled('type');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.movements_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('inventory.movements_subtitle') }}</p>
        </div>
        @can('manage inventory')
            <a href="{{ route('inventory.movements.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md dark:bg-[#3B82F6]">{{ __('inventory.btn_record_movement') }}</a>
        @endcan
    </div>

    <x-inventory.nav />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-sky-200/90 bg-sky-50/50 p-4 shadow-sm dark:border-sky-900/40 dark:bg-sky-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-sky-800 dark:text-sky-300">{{ __('inventory.stat_movements_today') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-sky-900 dark:text-sky-200">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('inventory.stat_inbound_30d') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">{{ number_format($stats['inbound']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('inventory.stat_outbound_30d') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['outbound']) }}</p>
        </div>
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('inventory.stat_consumption_30d') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['consumption']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.filter_results') }}</p>
        <form method="GET" action="{{ route('inventory.movements.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('inventory.filter_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-5">
                        <label for="movement_item_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_name') }}</label>
                        <select name="inventory_item_id" id="movement_item_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('inventory.filter_all') }}</option>
                            @foreach ($items ?? [] as $it)
                                <option value="{{ $it->id }}" @selected((string) request('inventory_item_id') === (string) $it->id)>{{ $it->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-4">
                        <label for="movement_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.type') }}</label>
                        <select name="type" id="movement_type"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('inventory.filter_all') }}</option>
                            @foreach ($typeLabels as $code => $label)
                                <option value="{{ $code }}" @selected(request('type') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-3 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('common.search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('inventory.movements.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('common.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.list_heading_movements') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                @if ($movements->total() > 0)
                    <p class="m-0 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('inventory.results_count', ['from' => $movements->firstItem(), 'to' => $movements->lastItem(), 'total' => $movements->total()]) }}
                    </p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-[#374151]">
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('common.type') }}</th>
                            <th class="px-4 py-3 text-end font-bold">{{ __('inventory.field_quantity') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('common.date') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.th_visit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $m)
                            @php
                                $qty = (float) $m->quantity;
                                $isInbound = \App\Support\Inventory\InventoryMovementType::increasesStock($m->type);
                            @endphp
                            <tr class="border-b border-gray-50 hover:bg-slate-50/80 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $m->item?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-inventory.movement-type-badge :type="$m->type" :label="$typeLabels[$m->type] ?? $m->type" />
                                </td>
                                <td class="px-4 py-3 text-end tabular-nums font-bold {{ $isInbound ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-800 dark:text-amber-300' }}">
                                    {{ $isInbound ? '+' : '−' }}{{ number_format(abs($qty), 3) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $m->movement_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @if ($m->visit_id)
                                        <a href="{{ route('visits.show', $m->visit_id) }}" data-spa class="text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">#{{ $m->visit_id }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-14 text-center">
                                    <p class="m-0 font-semibold text-slate-600 dark:text-slate-300">{{ __('inventory.empty_movements_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500">{{ __('inventory.empty_movements') }}</p>
                                    @can('manage inventory')
                                        <a href="{{ route('inventory.movements.create') }}" data-spa class="mt-4 inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('inventory.btn_record_movement') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())
                <div class="border-t px-4 py-3 dark:border-[#374151]">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>
</div>
