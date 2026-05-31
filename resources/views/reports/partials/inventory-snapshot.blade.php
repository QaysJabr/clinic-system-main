<p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.section_inventory') }}</p>
<div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-violet-500 dark:border-[#374151] dark:bg-[#1F2937]">
        <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_inventory_valuation') }}</p>
        <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($inventoryValuation ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base text-slate-500">{{ $cur }}</span>@endif</p>
        <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_inventory_valuation_hint') }}</p>
        <a href="{{ route('inventory.reports.index') }}" data-spa class="mt-2 inline-block text-xs font-bold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('reports.link_inventory_detail') }}</a>
    </div>
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-teal-600 dark:border-[#374151] dark:bg-[#1F2937]">
        <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_inventory_purchases_month') }}</p>
        <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($inventoryPurchasesThisMonth ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base text-slate-500">{{ $cur }}</span>@endif</p>
        <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_inventory_purchases_month_hint') }}</p>
    </div>
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-amber-500 dark:border-[#374151] dark:bg-[#1F2937]">
        <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_inventory_low_stock') }}</p>
        <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($inventoryLowStockCount ?? 0) }}</p>
        <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('inventory.kpi_low_stock') }} · {{ number_format($inventoryTotalSkus ?? 0) }} {{ __('reports.kpi_inventory_skus_label') }}</p>
    </div>
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-rose-500 dark:border-[#374151] dark:bg-[#1F2937]">
        <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_inventory_expiring') }}</p>
        <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-rose-700 dark:text-rose-300">{{ number_format($inventoryExpiringCount ?? 0) }}</p>
        <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('inventory.kpi_expiring') }}</p>
    </div>
</div>
