@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="max-w-[1400px] mx-auto" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex flex-wrap items-start gap-4 min-w-0">
                    @if($clinic->logoPublicUrl())
                        <img src="{{ $clinic->logoPublicUrl() }}" alt="" class="h-14 max-w-[160px] object-contain shrink-0">
                    @endif
                    <div class="min-w-0">
                    <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('reports.page_heading') }}</h1>
                    <p class="text-[#0F4C81] font-semibold text-sm mt-1 m-0">{{ $clinic->clinic_name ?: config('app.name') }}</p>
                    <p class="mt-2 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('reports.hero_subtitle') }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-[#9CA3AF] m-0">{{ __('reports.last_updated_at') }} {{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('reports.print', request()->query()) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-[#0F4C81]/20 hover:bg-[#0c3d6b] dark:bg-[#3B82F6] dark:shadow-lg dark:shadow-blue-900/20 dark:hover:bg-blue-600">{{ __('reports.nav_print') }}</a>
                    <a href="{{ route('reports.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-xl border border-[#0F4C81] bg-white px-5 py-2.5 text-sm font-bold text-[#0F4C81] shadow-sm hover:bg-blue-50 dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD] dark:shadow-none dark:hover:bg-[#1E3A5F]/50">{{ __('reports.nav_pdf') }}</a>
                    <a href="{{ route('reports.excel', request()->query()) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-[#1F7A8C] bg-white px-5 py-2.5 text-sm font-bold text-[#1F7A8C] shadow-sm hover:bg-teal-50 dark:border-teal-500/50 dark:bg-[#111827] dark:text-teal-300 dark:shadow-none dark:hover:bg-teal-950/40">{{ __('reports.nav_excel') }}</a>
                </div>
            </div>
        </div>

        <x-reports.nav active="financial" />

        <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('reports.section_profitability') }}</p>
        <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-emerald-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-emerald-500/75 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_total_accrual_revenue') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($totalAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_total_accrual_revenue_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-rose-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-rose-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_accounts_receivable') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-rose-700 dark:text-rose-300">{{ number_format($totalAccountsReceivable, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_accounts_receivable_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-cyan-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-cyan-500/80 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_doctor_share') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-cyan-900 dark:text-cyan-300">{{ number_format($totalDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_doctor_share_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-indigo-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-indigo-500/75 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_gross_profit') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-indigo-800 dark:text-indigo-300">{{ number_format($grossProfit, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_gross_profit_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-amber-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-amber-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('expenses.reports_label_expenses_commitments_short') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($totalExpensesAll, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('expenses.reports_row_expenses_table_total') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-violet-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-violet-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('payroll.reports_table_row_accrued_payroll') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($totalPayrollPaid, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('payroll.reports_summary_payroll_accrual_short') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-slate-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-slate-500/80 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('payroll.reports_operating_cost_accrual_title') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-100">{{ number_format($totalOperatingCost, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('payroll.reports_operating_cost_accrual_sub') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 {{ $netProfit < 0 ? 'border-t-red-500' : 'border-t-[#0F4C81]' }} dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_net_profit') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums {{ $netProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-[#0F4C81] dark:text-[#93C5FD]' }}">{{ number_format($netProfit, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_net_profit_hint') }}</p>
            </div>
        </div>

        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.section_cash_flow') }}</p>
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-teal-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-teal-500/75 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_patient_cash_in') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($totalPatientCashIn, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_patient_cash_in_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-slate-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-slate-500/80 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_total_cash_out') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-100">{{ number_format($totalCashOutReport, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('payroll.reports_cashflow_out_sub') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-sky-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-sky-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_net_cash_flow_cumulative') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums {{ $netCashFlowLifetime < 0 ? 'text-red-600 dark:text-red-400' : 'text-sky-800 dark:text-sky-300' }}">{{ number_format($netCashFlowLifetime, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_net_cash_flow_cumulative_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-teal-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-teal-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_cash_till') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($cashBalance, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_cash_balance_settings_hint') }}</p>
            </div>
        </div>
        <p class="mb-4 text-xs text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.cross_check_invoice_paid_vs_payments', ['amount' => number_format($totalCashCollected, 2).(filled($cur) ? ' '.$cur : '')]) }}</p>
        </div>

        @if($showInventoryReports ?? false)
        @include('reports.partials.inventory-snapshot')
        @endif

        <div class="mb-10 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
            <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]">
                <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.subsection_profitability_summary') }}</h3>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('expenses.reports_accrual_snapshot_legend') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_metric') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_today') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_this_month') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_grand_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_accrual_revenue_detail') }}</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($todayAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($thisMonthAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-800 dark:text-emerald-300">{{ number_format($totalAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_doctor_share_table') }}</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($todayDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($thisMonthDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-900 dark:text-cyan-300">{{ number_format($totalDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_accrued_expenses') }}</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($todayAccrualExpensesRecorded, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($thisMonthAccrualExpensesRecorded, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($totalExpensesAll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_accrued_payroll') }}</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($todayAccrualPayrollRecorded, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($thisMonthAccrualPayrollRecorded, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($profitabilitySnapshot['totalSalaries'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_gross_profit') }}</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($todayGrossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($thisMonthGrossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-800 dark:text-indigo-300">{{ number_format($grossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_total_operating_cost_accrual') }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($todayOperatingCostFull, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($thisMonthOperatingCostFull, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-800 dark:text-slate-200">{{ number_format($totalOperatingCost, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        @if($showInventoryReports ?? false)
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-violet-50/40 dark:bg-violet-950/15">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">
                                {{ __('reports.row_inventory_valuation') }}
                                <span class="block text-xs font-normal text-slate-500 dark:text-[#9CA3AF]">{{ __('reports.row_inventory_valuation_hint') }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">—</td>
                            <td class="px-4 py-3 text-slate-400">—</td>
                            <td class="px-4 py-3 tabular-nums font-bold text-violet-800 dark:text-violet-300">{{ number_format($inventoryValuation ?? 0, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_net_profit_plain') }}</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $todayNetProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($todayNetProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $thisMonthProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($thisMonthProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $netProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-[#0F4C81] dark:text-[#93C5FD]' }}">{{ number_format($netProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-10 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
            <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]">
                <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.subsection_cashflow_summary') }}</h3>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('payroll.reports_cashflow_out_sub') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_metric') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_today') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_this_month') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_grand_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_cash_patients_detail') }}</td>
                            <td class="px-4 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($todayCashCollected, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($thisMonthCashCollected, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($totalPatientCashIn, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_expense_cash_out') }}</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($todayExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($thisMonthExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($cashFlowSnapshot['cashOutExpensesTotal'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_payroll_cash_out') }}</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($todayPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($thisMonthPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($cashFlowSnapshot['cashOutPayrollTotal'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_doctor_payouts_cash') }}</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($todayDoctorPayoutsCash, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($thisMonthDoctorPayoutsCash, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-900 dark:text-cyan-300">{{ number_format($cashFlowSnapshot['cashOutDoctorsTotal'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_total_cash_out') }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($todayCashPaidOutReport, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($thisMonthCashPaidOutReport, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-800 dark:text-slate-200">{{ number_format($totalCashOutReport, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_net_cash_flow_cumulative') }}</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $todayNetCashFlowReport < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($todayNetCashFlowReport, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $thisMonthNetCashFlowReport < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($thisMonthNetCashFlowReport, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $netCashFlowLifetime < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($netCashFlowLifetime, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_cash_balance_detailed') }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400">{{ __('common.em_dash') }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400">{{ __('common.em_dash') }}</td>
                            <td class="px-4 py-3 tabular-nums font-semibold text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($cashBalance, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.section_extended') }}</p>
        <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-slate-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-slate-500/80 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_total_cash_out_all_time_title') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-slate-800 dark:text-[#F3F4F6]">{{ number_format($totalOperatingOutflows, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('payroll.reports_row_expense_payroll_cash_sub') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-rose-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-rose-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_receivable_exposure_title') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-rose-700 dark:text-rose-300">{{ number_format($totalReceivableExposure, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_receivable_exposure_hint') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-sky-500 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-sky-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_ytd_net_profit') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums {{ $ytdNetProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-sky-800 dark:text-sky-300' }}">{{ number_format($ytdNetProfit, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('expenses.reports_cashflow_mix_sub') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-teal-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-teal-500/70 dark:shadow-sm">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_cash_balance') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($cashBalance, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('reports.kpi_cash_balance_settings_hint') }}</p>
            </div>
        </div>

        <div class="mb-10 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
            <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]">
                <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.section_period_comparison') }}</h3>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('payroll.reports_profitability_note') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_metric') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_last_month') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_this_month') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_ytd') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.th_last_30_days') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_accrual_revenue_detail') }}</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($lastMonthAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($thisMonthAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($ytdAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($last30AccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_patient_cash_in') }}</td>
                            <td class="px-4 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($lastMonthCashCollected, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($thisMonthCashCollected, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($ytdCashCollected, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($last30CashCollected, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_doctor_share_expense_comparison') }}</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($lastMonthDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($thisMonthDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($ytdDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($last30DoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_accrued_expenses') }}</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($lastMonthAccrualExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($thisMonthAccrualExpensesRecorded, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($ytdAccrualExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($last30AccrualExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_accrued_payroll') }}</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($lastMonthAccrualPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($thisMonthAccrualPayrollRecorded, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($ytdAccrualPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($last30AccrualPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_expense_cash_out') }}</td>
                            <td class="px-4 py-3 tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($lastMonthExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($thisMonthExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($ytdExpenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-amber-800 dark:text-amber-300">{{ number_format($last30Expenses, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('payroll.reports_table_row_payroll_cash_out') }}</td>
                            <td class="px-4 py-3 tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($lastMonthPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($thisMonthPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($ytdPayroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-violet-800 dark:text-violet-300">{{ number_format($last30Payroll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_doctor_payouts_cash') }}</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($lastMonthDoctorPayoutsCash, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($thisMonthDoctorPayoutsCash, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($ytdDoctorPayoutsCash, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($last30DoctorPayoutsCash, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_gross_profit') }}</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($lastMonthGrossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($thisMonthGrossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($ytdGrossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($last30GrossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.row_total_operating_cost_accrual') }} <span class="block text-xs font-normal text-slate-500 dark:text-[#94A3B8]">{{ __('reports.operating_cost_accrual_subline') }}</span></td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($lastMonthOperatingCostFull, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($thisMonthOperatingCostFull, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($ytdOperatingCostFull, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($last30OperatingCostFull, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_total_cash_out') }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($lastMonthCashOut, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($thisMonthCashPaidOutReport, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($ytdCashOut, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($last30CashOut, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_net_cash_flow_cumulative') }}</td>
                            <td class="px-4 py-3 tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($lastMonthNetCashFlow, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($thisMonthNetCashFlowReport, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($ytdNetCashFlow, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums text-teal-800 dark:text-teal-300">{{ number_format($last30NetCashFlow, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('reports.kpi_net_profit_accrual') }}</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $lastMonthProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($lastMonthProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $thisMonthProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($thisMonthProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $ytdNetProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($ytdNetProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 tabular-nums font-semibold {{ $last30NetProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($last30NetProfit, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-10 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
            <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]">
                <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.section_monthly_trend') }}</h3>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('reports.section_monthly_trend_hint') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1400px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.pdf_th_month') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.print_short_accrual_revenue') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.kpi_doctor_share_table_short') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.kpi_gross_profit') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('expenses.reports_th_expenses_accrual') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('payroll.reports_th_payroll_accrual') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.kpi_total_operating_cost_accrual') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.kpi_net_profit_accrual') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.pdf_th_collect_cash_short') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.pdf_th_out_cash_short') }}</th>
                            <th class="px-3 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.pdf_th_net_flow_short') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyFinancialTrend as $row)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $row['label'] }}</td>
                                <td class="px-3 py-3 tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($row['accrual_revenue'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-cyan-800 dark:text-cyan-400">{{ number_format($row['doctor_share'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($row['gross_profit'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($row['expenses_accrual'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-violet-700 dark:text-violet-400">{{ number_format($row['payroll_accrual'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($row['operating_cost_accrual'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums font-semibold {{ $row['net_profit_accrual'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($row['net_profit_accrual'], 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-teal-700 dark:text-teal-400">{{ number_format($row['cash_in_patients'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums text-slate-600 dark:text-slate-400">{{ number_format($row['cash_paid_out_total'], 2) }}@if(filled($cur)) <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                                <td class="px-3 py-3 tabular-nums font-semibold {{ $row['net_cash_flow'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-sky-700 dark:text-sky-300' }}">{{ number_format($row['net_cash_flow'], 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('invoices.reports_heading_collection_by_method') }}</p>
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($incomeByPaymentMethod as $method => $amt)
                <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-[#1F7A8C] dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-teal-500/70 dark:shadow-sm">
                    <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ \App\Support\PaymentMethods::label((string) $method) }}</p>
                    <p class="mt-2 mb-0 text-xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($amt, 2) }}@if(filled($cur)) <span class="text-sm text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                </div>
            @endforeach
        </div>

        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.excel_heading_expense_by_pm') }}</p>
        <div class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($expenseOutflowsByPaymentMethod as $method => $amt)
                <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-amber-600 dark:border-[#374151] dark:bg-[#1F2937] dark:border-t-amber-500/70 dark:shadow-sm">
                    <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ \App\Support\PaymentMethods::label((string) $method) }}</p>
                    <p class="mt-2 mb-0 text-xl font-bold tabular-nums text-amber-900 dark:text-amber-300">{{ number_format($amt, 2) }}@if(filled($cur)) <span class="text-sm text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                </div>
            @endforeach
        </div>

        {{-- ملخص مالي: 3 بطاقات — شريط علوي ملوّن بالوضع الفاتح (مثل التصميم المرجعي) --}}
        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('invoices.reports_financial_cards_title') }}</p>
        <div class="mb-10 grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-emerald-500 dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:border-t-emerald-500/75">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('invoices.reports_card_paid_full_title') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ number_format($paidAmount, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('invoices.reports_card_paid_full_sub') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-amber-500 dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:border-t-amber-500/75">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('invoices.reports_card_partial_remaining_title') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-amber-600 dark:text-amber-400">{{ number_format($partialAmount, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('invoices.reports_card_partial_remaining_sub') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md border-t-4 border-t-red-500 dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:border-t-red-500/75">
                <p class="m-0 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('invoices.reports_card_unpaid_title') }}</p>
                <p class="mt-2 mb-0 text-2xl font-bold tabular-nums text-red-600 dark:text-red-400">{{ number_format($unpaidAmount, 2) }}@if(filled($cur)) <span class="text-base text-slate-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
                <p class="mt-1 m-0 text-xs text-slate-500 dark:text-[#94A3B8]">{{ __('invoices.reports_card_unpaid_sub') }}</p>
            </div>
        </div>

        {{-- 6 مؤشرات تشغيلية --}}
        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.kpi_heading_general') }}</p>
        <div class="mb-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
                <p class="text-gray-500 dark:text-[#9CA3AF] text-sm m-0 font-medium">{{ __('reports.excel_row_total_patients') }}</p>
                <p class="text-[#0F4C81] dark:text-[#93C5FD] text-3xl font-bold mt-2 mb-0 tabular-nums">{{ $totalPatients }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
                <p class="text-gray-500 dark:text-[#9CA3AF] text-sm m-0 font-medium">{{ __('reports.excel_row_total_doctors') }}</p>
                <p class="text-[#1F7A8C] dark:text-teal-300 text-3xl font-bold mt-2 mb-0 tabular-nums">{{ $totalDoctors }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
                <p class="text-gray-500 dark:text-[#9CA3AF] text-sm m-0 font-medium">{{ __('reports.excel_row_total_appointments') }}</p>
                <p class="text-green-700 dark:text-green-400 text-3xl font-bold mt-2 mb-0 tabular-nums">{{ $totalAppointments }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
                <p class="text-gray-500 dark:text-[#9CA3AF] text-sm m-0 font-medium">{{ __('visits.report_total_visits_label') }}</p>
                <p class="text-indigo-600 dark:text-indigo-400 text-3xl font-bold mt-2 mb-0 tabular-nums">{{ $totalVisits }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
                <p class="text-gray-500 dark:text-[#9CA3AF] text-sm m-0 font-medium">{{ __('invoices.reports_total_invoices_kpi') }}</p>
                <p class="text-amber-600 dark:text-amber-400 text-3xl font-bold mt-2 mb-0 tabular-nums">{{ $totalInvoices }}</p>
            </div>
        </div>

        @php($hasExpenseFilters = request()->filled('report_expense_category_id') || request()->filled('report_expense_date_from') || request()->filled('report_expense_date_to'))

        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.section_expense_filter') }}</p>
        <form method="GET" action="{{ route('reports.index') }}" class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="text-base font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('reports.expense_filter_heading') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-4">
                        <label for="report_expense_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.filter_category') }}</label>
                        <select name="report_expense_category_id" id="report_expense_category_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('reports.filter_all') }}</option>
                            @foreach(($expenseCategoriesForReportFilter ?? collect()) as $ec)
                                <option value="{{ $ec->id }}" {{ (string) ($reportExpenseCategoryId ?? '') === (string) $ec->id ? 'selected' : '' }}>{{ $ec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label for="report_expense_date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.filter_date_from') }}</label>
                        <input type="date" name="report_expense_date_from" id="report_expense_date_from" value="{{ $reportExpenseDateFrom }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-3">
                        <label for="report_expense_date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.filter_date_to') }}</label>
                        <input type="date" name="report_expense_date_to" id="report_expense_date_to" value="{{ $reportExpenseDateTo }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-2 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('reports.btn_apply') }}</button>
                        <a href="{{ route('reports.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('reports.btn_reset') }}</a>
                    </div>
                </div>
                @if($hasExpenseFilters)
                    <p class="mt-4 m-0 text-sm font-semibold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.expense_filtered_total_line') }} <span class="tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($expenseFilteredTotal, 2) }}@if(filled($cur)) {{ $cur }}@endif</span></p>
                @endif
            </div>
        </form>

        <div class="mb-10 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
            <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]">
                <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.section_expense_distribution') }}{{ $hasExpenseFilters ? __('reports.expense_distribution_with_filter_suffix') : '' }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[400px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('expenses.field_category') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.pdf_th_total_short') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($expenseSummaryByCategory ?? collect()) as $row)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $row->category_name }}</td>
                                <td class="px-4 py-3 font-semibold tabular-nums text-slate-800 dark:text-slate-100">{{ number_format((float) $row->total_amount, 2) }}@if(filled($cur)) <span class="text-xs font-normal text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.empty_no_matching_expenses') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- توزيع الفواتير حسب الحالة — بطاقات بيضاء بشريط علوي (فاتح) / تدرج هادئ (داكن) --}}
        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('invoices.reports_heading_status_distribution') }}</p>
        <div class="mb-10 grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200/90 bg-white p-6 shadow-md border-t-4 border-t-red-500 dark:border-[#374151] dark:bg-gradient-to-br dark:from-red-950/40 dark:to-[#1F2937] dark:shadow-sm dark:border-t-red-500/70">
                <h3 class="m-0 text-base font-bold text-red-800 dark:text-red-200">{{ __('invoices.reports_status_heading_unpaid') }}</h3>
                <p class="mt-3 mb-0 text-4xl font-bold tabular-nums text-red-600 dark:text-red-400">{{ $unpaidInvoices }}</p>
                <p class="mt-2 m-0 text-sm text-red-700/90 dark:text-red-300/90">{{ __('invoices.reports_status_amount_prefix') }} {{ number_format($unpaidAmount, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-6 shadow-md border-t-4 border-t-amber-500 dark:border-[#374151] dark:bg-gradient-to-br dark:from-amber-950/35 dark:to-[#1F2937] dark:shadow-sm dark:border-t-amber-500/70">
                <h3 class="m-0 text-base font-bold text-amber-900 dark:text-amber-200">{{ __('invoices.reports_status_heading_partial') }}</h3>
                <p class="mt-3 mb-0 text-4xl font-bold tabular-nums text-amber-600 dark:text-amber-400">{{ $partialInvoices }}</p>
                <p class="mt-2 m-0 text-sm text-amber-800 dark:text-amber-200/90">{{ __('invoices.reports_status_remaining_prefix') }} {{ number_format($partialAmount, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-6 shadow-md border-t-4 border-t-emerald-500 dark:border-[#374151] dark:bg-gradient-to-br dark:from-emerald-950/35 dark:to-[#1F2937] dark:shadow-sm dark:border-t-emerald-500/70">
                <h3 class="m-0 text-base font-bold text-emerald-900 dark:text-emerald-200">{{ __('invoices.reports_status_heading_paid') }}</h3>
                <p class="mt-3 mb-0 text-4xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $paidInvoices }}</p>
                <p class="mt-2 m-0 text-sm text-emerald-800 dark:text-emerald-200/90">{{ __('invoices.reports_status_amount_prefix') }} {{ number_format($paidAmount, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
            </div>
        </div>

        {{-- جداول تفصيلية — بطاقات بظل أوضح في الوضع الفاتح --}}
        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8] m-0">{{ __('reports.section_recent_activity') }}</p>
        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
                <div class="bg-[#0F4C81] px-4 py-3 text-white shadow-sm dark:shadow-none">
                    <h3 class="text-base font-bold m-0">{{ __('reports.recent_patients_heading') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[280px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.recent_patients_th_name') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.recent_patients_th_phone') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.recent_patients_th_registered') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPatients as $patient)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-[#111827]/80">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $patient->full_name }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ $patient->phone ?? __('common.em_dash') }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">@safeDate($patient->created_at)</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.empty_no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
                <div class="bg-[#1F7A8C] px-4 py-3 text-white shadow-sm dark:shadow-none">
                    <h3 class="text-base font-bold m-0">{{ __('reports.recent_appointments_heading') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[280px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.recent_appointments_th_patient') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.recent_appointments_th_doctor') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('reports.recent_appointments_th_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAppointments as $appointment)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-[#111827]/80">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $appointment->patient->full_name ?? __('common.em_dash') }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ $appointment->doctor->full_name ?? __('common.em_dash') }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">@safeDate($appointment->appointment_date)</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.empty_no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
                <div class="bg-amber-600 px-4 py-3 text-white shadow-sm dark:shadow-none">
                    <h3 class="text-base font-bold m-0">{{ __('invoices.reports_recent_invoices') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[280px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('invoices.field_invoice_number') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('invoices.field_patient') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('invoices.field_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $invoice)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-[#111827]/80">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ $invoice->patient->full_name ?? __('common.em_dash') }}</td>
                                    <td class="px-4 py-3 font-bold {{ $invoice->status == 'paid' ? 'text-emerald-600' : ($invoice->status == 'partial' ? 'text-amber-600' : 'text-red-600') }}">{{ number_format((float) $invoice->total, 2) }}@if(filled($cur)) <span class="text-gray-500 dark:text-[#9CA3AF] font-medium">{{ $cur }}</span>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.empty_no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md ring-1 ring-slate-900/[0.03] dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm dark:ring-0">
                <div class="bg-rose-700 px-4 py-3 text-white shadow-sm dark:shadow-none">
                    <h3 class="text-base font-bold m-0">{{ __('payments.reports_recent_payments') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-200/90 bg-slate-50/95 dark:border-[#374151] dark:bg-[#111827]">
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('invoices.field_patient') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('payments.reports_th_invoice') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('common.amount') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('payments.reports_th_method') }}</th>
                                <th class="px-4 py-3 text-right font-bold text-slate-700 dark:text-[#E5E7EB]">{{ __('payments.reports_th_payment_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-[#111827]/80">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ optional(optional($payment->invoice)->patient)->full_name ?? __('common.em_dash') }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ optional($payment->invoice)->invoice_number ?? __('common.em_dash') }}</td>
                                    <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">{{ number_format((float) $payment->amount, 2) }}@if(filled($cur)) <span class="text-gray-500 dark:text-[#9CA3AF] font-medium">{{ $cur }}</span>@endif</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ \App\Support\PaymentMethods::label((string) ($payment->payment_method ?? '')) }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">@safeDate($payment->payment_date)</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.empty_no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(filled($clinic->report_footer))
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-5 mb-8 dark:border-[#4B5563] dark:bg-[#111827]/80">
                <p class="text-sm font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('reports.section_report_footer_heading') }}</p>
                <p class="text-sm text-gray-700 dark:text-[#E5E7EB] mt-2 m-0 whitespace-pre-wrap leading-relaxed">{{ $clinic->report_footer }}</p>
            </div>
        @endif

        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-sm">
            <h3 class="m-0 mb-4 text-base font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('reports.section_quick_links') }}</h3>
            <div class="flex flex-wrap gap-2">
                @can('view dashboard')
                <a href="{{ route('dashboard') }}" class="rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-800 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#E5E7EB] hover:bg-gray-100 dark:hover:bg-[#374151]">{{ __('reports.quick_dashboard') }}</a>
                @endcan
                @can('manage patients')
                <a href="{{ route('patients.index') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ __('patients.title') }}</a>
                @endcan
                @can('manage doctors')
                <a href="{{ route('doctors.index') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('doctors.title') }}</a>
                @endcan
                @can('manage appointments')
                <a href="{{ route('appointments.index') }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">{{ __('appointments.nav_appointments') }}</a>
                @endcan
                @can('manage visits')
                <a href="{{ route('visits.index') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">{{ __('visits.nav_visits') }}</a>
                @endcan
                @can('manage invoices')
                <a href="{{ route('invoices.index') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">{{ __('invoices.reports_quick_link') }}</a>
                @endcan
                @can('manage expenses')
                <a href="{{ route('expenses.index') }}" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">{{ __('expenses.nav') }}</a>
                @endcan
                @can('manage expense categories')
                <a href="{{ route('expense-categories.index') }}" class="rounded-lg border border-slate-600 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-500 dark:bg-[#111827] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('expenses.nav_categories') }}</a>
                @endcan
            </div>
        </div>
    </div>
