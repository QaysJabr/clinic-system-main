@php
    $monthProfit = (float) ($thisMonthNetProfit ?? $netProfit ?? 0);
    $profitAccent = $monthProfit < 0 ? 'red' : 'brand';
@endphp
<x-dashboard.section-label bar="emerald">{{ __('dashboard.section_financial_glance') }}</x-dashboard.section-label>
<div class="mb-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
    <x-dashboard.metric-card accent="rose" :title="__('reports.kpi_accounts_receivable')" :hint="__('reports.kpi_accounts_receivable_hint')">
        {{ number_format($totalAccountsReceivable ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
        <x-slot:footer>
            <a href="{{ route('reports.receivables') }}" class="text-xs font-bold text-rose-700 hover:underline dark:text-rose-300" data-spa>{{ __('dashboard.snapshot_ar_link') }} →</a>
        </x-slot:footer>
    </x-dashboard.metric-card>

    <x-dashboard.metric-card :accent="$profitAccent" :title="__('dashboard.snapshot_month_profit')" :hint="__('dashboard.snapshot_month_profit_hint')">
        {{ number_format($monthProfit, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
    </x-dashboard.metric-card>

    <x-dashboard.metric-card accent="teal" :title="__('reports.kpi_cash_till')" :hint="__('reports.kpi_cash_balance_settings_hint')">
        {{ number_format($cashBalance ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
    </x-dashboard.metric-card>
</div>
<div class="mb-2">
    <a href="{{ route('reports.index') }}" data-spa class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-[#0F4C81]/25 hover:bg-[#0c3d6b] dark:bg-blue-600 dark:shadow-blue-900/30 dark:hover:bg-blue-500">
        {{ __('dashboard.cta_full_reports') }} →
    </a>
</div>
