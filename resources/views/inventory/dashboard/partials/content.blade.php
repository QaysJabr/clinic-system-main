@if(! empty($pageTitle ?? null))

    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>

@endif

<div class="max-w-[1400px] mx-auto" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">

    @include('inventory.partials.nav')



    <div class="mb-8">

        <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('inventory.dashboard_title') }}</h1>

        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 m-0">{{ __('inventory.dashboard_subtitle') }}</p>

    </div>



    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-violet-500 dark:border-[#374151] dark:bg-[#1F2937]">

            <p class="m-0 text-sm text-slate-600 dark:text-slate-400">{{ __('inventory.kpi_total_skus') }}</p>

            <p class="mt-2 mb-0 text-3xl font-bold tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($total_skus ?? 0) }}</p>

        </div>

        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-amber-500 dark:border-[#374151] dark:bg-[#1F2937]">

            <p class="m-0 text-sm text-slate-600 dark:text-slate-400">{{ __('inventory.kpi_low_stock') }}</p>

            <p class="mt-2 mb-0 text-3xl font-bold tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($low_stock_count ?? 0) }}</p>

        </div>

        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-rose-500 dark:border-[#374151] dark:bg-[#1F2937]">

            <p class="m-0 text-sm text-slate-600 dark:text-slate-400">{{ __('inventory.kpi_expiring') }}</p>

            <p class="mt-2 mb-0 text-3xl font-bold tabular-nums text-rose-700 dark:text-rose-300">{{ number_format($expiring_soon_count ?? 0) }}</p>

        </div>

        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-emerald-500 dark:border-[#374151] dark:bg-[#1F2937]">

            <p class="m-0 text-sm text-slate-600 dark:text-slate-400">{{ __('inventory.kpi_valuation') }}</p>

            <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-emerald-700 dark:text-emerald-400">

                {{ number_format($valuation ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base text-slate-500">{{ $cur }}</span>@endif

            </p>

        </div>

    </div>



    @can('manage inventory')

    <div class="mb-8 flex flex-wrap gap-2">

        <a href="{{ route('inventory.items.create') }}" data-spa class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-[#0c3d6b] dark:bg-blue-600">{{ __('inventory.btn_add_item') }}</a>

        <a href="{{ route('inventory.purchases.create') }}" data-spa class="inline-flex items-center justify-center rounded-xl border border-teal-600 bg-white px-4 py-2.5 text-sm font-bold text-teal-800 dark:bg-[#111827] dark:text-teal-300">{{ __('inventory.btn_receive_stock') }}</a>

        <a href="{{ route('inventory.movements.create') }}" data-spa class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 dark:border-[#374151] dark:bg-[#111827] dark:text-slate-200">{{ __('inventory.btn_record_movement') }}</a>

        <a href="{{ route('inventory.templates.create') }}" data-spa class="inline-flex items-center justify-center rounded-xl border border-violet-300 bg-white px-4 py-2.5 text-sm font-bold text-violet-900 dark:bg-[#111827] dark:text-violet-300">{{ __('inventory.btn_add_template') }}</a>

    </div>

    @endcan



    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 mb-8">

        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">

            <div class="border-b border-amber-100 bg-amber-50/80 px-4 py-3 flex justify-between items-center dark:border-amber-900/40 dark:bg-amber-950/30">

                <h3 class="m-0 text-sm font-bold text-amber-900 dark:text-amber-200">{{ __('inventory.section_low_stock') }}</h3>

                <a href="{{ route('inventory.items.index', ['low_stock' => 1]) }}" data-spa class="text-xs font-bold text-amber-800 hover:underline">{{ __('inventory.link_view_all') }}</a>

            </div>

            <ul class="m-0 divide-y divide-slate-100 p-0 list-none dark:divide-[#374151]">

                @forelse($low_stock_items ?? [] as $item)

                    <li class="px-4 py-3 flex justify-between gap-2">

                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $item->name }}</span>

                        <span class="tabular-nums text-amber-800 dark:text-amber-300 font-bold">{{ number_format((float) $item->quantity_on_hand, 3) }}</span>

                    </li>

                @empty

                    <li class="px-4 py-8 text-center text-sm text-slate-500">{{ __('inventory.empty_items') }}</li>

                @endforelse

            </ul>

        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">

            <div class="border-b border-rose-100 bg-rose-50/80 px-4 py-3 dark:border-rose-900/40 dark:bg-rose-950/30">

                <h3 class="m-0 text-sm font-bold text-rose-900 dark:text-rose-200">{{ __('inventory.section_expiring') }}</h3>

            </div>

            <ul class="m-0 divide-y divide-slate-100 p-0 list-none dark:divide-[#374151]">

                @forelse($expiring_items ?? [] as $item)

                    <li class="px-4 py-3 flex justify-between gap-2">

                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $item->name }}</span>

                        <span class="text-sm text-rose-700 dark:text-rose-300">{{ $item->expiry_date?->format('d/m/Y') }}</span>

                    </li>

                @empty

                    <li class="px-4 py-8 text-center text-sm text-slate-500">—</li>

                @endforelse

            </ul>

        </div>

    </div>



    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">

            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 flex justify-between items-center dark:border-[#374151] dark:bg-[#111827]">

                <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-slate-200">{{ __('inventory.section_recent_movements') }}</h3>

                <a href="{{ route('inventory.movements.index') }}" data-spa class="text-xs font-bold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('inventory.link_view_all') }}</a>

            </div>

            <ul class="m-0 divide-y divide-slate-100 p-0 list-none text-sm dark:divide-[#374151]">

                @forelse($recent_movements ?? [] as $movement)

                    <li class="px-4 py-2.5 flex justify-between gap-2">

                        <span class="text-slate-800 dark:text-slate-200">{{ $movement->item?->name ?? '—' }}</span>

                        <span class="tabular-nums font-bold text-slate-600 dark:text-slate-400">{{ number_format((float) $movement->quantity, 3) }}</span>

                    </li>

                @empty

                    <li class="px-4 py-8 text-center text-slate-500">—</li>

                @endforelse

            </ul>

        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">

            <div class="border-b border-violet-100 bg-violet-50/80 px-4 py-3 dark:border-violet-900/40 dark:bg-violet-950/30">

                <h3 class="m-0 text-sm font-bold text-violet-900 dark:text-violet-200">{{ __('inventory.section_top_consumed') }}</h3>

            </div>

            <ul class="m-0 divide-y divide-slate-100 p-0 list-none text-sm dark:divide-[#374151]">

                @forelse($top_consumed ?? [] as $row)

                    <li class="px-4 py-2.5 flex justify-between gap-2">

                        <span>{{ $row['item_name'] }}</span>

                        <span class="tabular-nums font-bold">{{ number_format($row['total_qty'], 3) }}</span>

                    </li>

                @empty

                    <li class="px-4 py-8 text-center text-slate-500">—</li>

                @endforelse

            </ul>

        </div>

    </div>

</div>

