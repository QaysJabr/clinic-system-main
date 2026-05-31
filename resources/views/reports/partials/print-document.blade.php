@section('content')
    @php
        $clinicTitle = $clinic->clinic_name ?: config('app.name', __('reports.pdf_fallback_clinic'));
        $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' Â· ');
        $cur = $clinic->currency;
        $hasExpenseFilters = request()->filled('report_expense_category_id') || request()->filled('report_expense_date_from') || request()->filled('report_expense_date_to');
    @endphp

    <div class="mb-8 border-b border-gray-200 pb-6">
        <div class="flex flex-wrap items-start gap-6">
            <div class="min-w-0 flex-1">
                <p class="m-0 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.pdf_tag') }}</p>
                <h1 class="m-0 mt-2 text-2xl font-bold text-[#0F4C81]">{{ $clinicTitle }}</h1>
                <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('reports.pdf_headline_sub') }}</p>
                @if($clinicContact !== '')
                    <p class="m-0 mt-1 text-xs text-gray-600 dark:text-[#9CA3AF]">{{ $clinicContact }}</p>
                @endif
                @if(filled($clinic->clinic_address))
                    <p class="m-0 mt-1 text-xs text-gray-500 dark:text-[#9CA3AF] whitespace-pre-wrap">{{ $clinic->clinic_address }}</p>
                @endif
                <p class="m-0 mt-2 text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.print_generated_at_label') }} {{ $generatedAt->format('d/m/Y H:i') }}</p>
            </div>
            @php
                $clinicLogoSrc = ($exportRender ?? false)
                    ? \App\Support\ClinicDocumentLogo::src($clinic, true)
                    : $clinic->logoPublicUrl();
            @endphp
            @if($clinicLogoSrc)
                <div class="shrink-0">
                    <img src="{{ $clinicLogoSrc }}" alt="" class="h-16 max-w-[180px] object-contain">
                </div>
            @endif
        </div>
    </div>

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.pdf_section_profitability') }}</p>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #10b981;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.print_short_accrual_revenue') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-emerald-700">{{ number_format($totalAccrualRevenue, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #e11d48;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.print_short_ar') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-rose-700">{{ number_format($totalAccountsReceivable, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #0891b2;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.kpi_doctor_share') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-cyan-900">{{ number_format($totalDoctorShareExpense, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #6366f1;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.kpi_gross_profit') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-indigo-800">{{ number_format($grossProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #f59e0b;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.registered_expenses') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-amber-800">{{ number_format($totalExpensesAll, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #7c3aed;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.registered_payroll') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-violet-800">{{ number_format($totalPayrollPaid, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #475569;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.kpi_total_operating_cost_accrual') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-slate-800">{{ number_format($totalOperatingCost, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid {{ $netProfit < 0 ? '#ef4444' : '#0F4C81' }};">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.kpi_net_profit_accrual') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums {{ $netProfit < 0 ? 'text-red-600' : 'text-[#0F4C81]' }}">{{ number_format($netProfit, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
    </div>
    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.section_cash_flow') }}</p>
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #0d9488;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.kpi_patient_cash_in') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-teal-800">{{ number_format($totalPatientCashIn, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #475569;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.pdf_th_out_cash_short') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-slate-800">{{ number_format($totalCashOutReport, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #0ea5e9;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.kpi_net_cash_flow_cumulative') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums {{ $netCashFlowLifetime < 0 ? 'text-red-600' : 'text-sky-800' }}">{{ number_format($netCashFlowLifetime, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #0f766e;">
            <p class="m-0 text-xs text-gray-600">{{ __('reports.pdf_cash_balance_short') }}</p>
            <p class="m-0 mt-1 text-xl font-bold tabular-nums text-teal-800">{{ number_format($cashBalance, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
        </div>
    </div>

    <p class="m-0 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.pdf_section_profitability_table') }}</p>
    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_metric') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_today') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_this_month') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_grand_total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_accrual_revenue_detail') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayAccrualRevenue, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthAccrualRevenue, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($totalAccrualRevenue, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_doctor_share_table') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayDoctorShareExpense, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthDoctorShareExpense, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($totalDoctorShareExpense, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_accrued_expenses') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayAccrualExpensesRecorded, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthAccrualExpensesRecorded, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($totalExpensesAll, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_accrued_payroll') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayAccrualPayrollRecorded, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthAccrualPayrollRecorded, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($profitabilitySnapshot['totalSalaries'], 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_gross_profit') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayGrossProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthGrossProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($grossProfit, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_total_operating_cost_accrual') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayOperatingCostFull, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthOperatingCostFull, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($totalOperatingCost, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-semibold">{{ __('reports.row_net_profit_plain') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $todayNetProfit < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($todayNetProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $thisMonthProfit < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($thisMonthProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $netProfit < 0 ? 'text-red-600' : 'text-[#0F4C81]' }}">{{ number_format($netProfit, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p class="m-0 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.pdf_section_cash_table') }}</p>
    <div class="mb-10 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_metric') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_today') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_this_month') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_grand_total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_cash_patients_detail') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayCashCollected, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthCashCollected, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($totalPatientCashIn, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_expense_cash_out') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($cashFlowSnapshot['cashOutExpensesTotal'], 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_payroll_cash_out') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($cashFlowSnapshot['cashOutPayrollTotal'], 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_doctor_payouts_cash') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayDoctorPayoutsCash, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthDoctorPayoutsCash, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($cashFlowSnapshot['cashOutDoctorsTotal'], 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_total_cash_out') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($todayCashPaidOutReport, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthCashPaidOutReport, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($totalCashOutReport, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-semibold">{{ __('reports.kpi_net_cash_flow_cumulative') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $todayNetCashFlowReport < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($todayNetCashFlowReport, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $thisMonthNetCashFlowReport < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($thisMonthNetCashFlowReport, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $netCashFlowLifetime < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($netCashFlowLifetime, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-semibold">{{ __('reports.kpi_cash_balance_detailed') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ __('common.em_dash') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ __('common.em_dash') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold text-[#0F4C81]">{{ number_format($cashBalance, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.section_extended') }}</p>
    <div class="mb-8 grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" style="border-top:4px solid #475569;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_total_cash_out_all_time_title') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-gray-900">{{ number_format($totalOperatingOutflows, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('payroll.reports_row_expense_payroll_cash_sub') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" style="border-top:4px solid #e11d48;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_receivable_exposure_title') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-rose-700">{{ number_format($totalReceivableExposure, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('reports.kpi_receivable_exposure_hint') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" style="border-top:4px solid #0ea5e9;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_ytd_net_profit') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums {{ $ytdNetProfit < 0 ? 'text-red-600' : 'text-sky-800' }}">{{ number_format($ytdNetProfit, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('expenses.reports_cashflow_mix_sub') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" style="border-top:4px solid #0f766e;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('reports.kpi_cash_balance') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-teal-800">{{ number_format($cashBalance, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('reports.kpi_cash_balance_settings_hint') }}</p>
        </div>
    </div>

    <p class="m-0 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.section_period_comparison') }}</p>
    <div class="mb-10 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_metric') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_last_month') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_this_month') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_ytd') }}</th>
                    <th class="border border-gray-200 px-3 py-2 text-right font-bold text-gray-700">{{ __('reports.th_last_30_days') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_accrual_revenue_detail') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthAccrualRevenue, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthAccrualRevenue, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdAccrualRevenue, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30AccrualRevenue, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_patient_cash_in') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthCashCollected, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthCashCollected, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdCashCollected, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30CashCollected, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_doctor_share_expense_comparison') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthDoctorShareExpense, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthDoctorShareExpense, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdDoctorShareExpense, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30DoctorShareExpense, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_accrued_expenses') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthAccrualExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthAccrualExpensesRecorded, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdAccrualExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30AccrualExpenses, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_accrued_payroll') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthAccrualPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthAccrualPayrollRecorded, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdAccrualPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30AccrualPayroll, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_expense_cash_out') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdExpenses, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30Expenses, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('payroll.reports_table_row_payroll_cash_out') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdPayroll, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30Payroll, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_doctor_payouts_cash') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthDoctorPayoutsCash, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthDoctorPayoutsCash, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdDoctorPayoutsCash, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30DoctorPayoutsCash, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_gross_profit') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthGrossProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthGrossProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdGrossProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30GrossProfit, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.row_total_operating_cost_accrual') }} <span class="block text-xs font-normal text-gray-500">{{ __('reports.operating_cost_accrual_subline') }}</span></td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthOperatingCostFull, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthOperatingCostFull, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdOperatingCostFull, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30OperatingCostFull, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_total_cash_out') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthCashOut, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthCashPaidOutReport, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdCashOut, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30CashOut, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_net_cash_flow_cumulative') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($lastMonthNetCashFlow, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($thisMonthNetCashFlowReport, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($ytdNetCashFlow, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums">{{ number_format($last30NetCashFlow, 2) }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 px-3 py-2 font-medium">{{ __('reports.kpi_net_profit_accrual') }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $lastMonthProfit < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($lastMonthProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $thisMonthProfit < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($thisMonthProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $ytdNetProfit < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($ytdNetProfit, 2) }}</td>
                    <td class="border border-gray-200 px-3 py-2 tabular-nums font-semibold {{ $last30NetProfit < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($last30NetProfit, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="m-0 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.section_monthly_trend') }}</p>
    <div class="mb-10 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.pdf_th_month') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.print_short_accrual_revenue') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.kpi_doctor_share_table_short') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.kpi_gross_profit') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('expenses.reports_th_expenses_accrual') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('payroll.reports_th_payroll_accrual') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.kpi_total_operating_cost_accrual') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.kpi_net_profit_accrual') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.pdf_th_collect_cash_short') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.pdf_th_out_cash_short') }}</th>
                    <th class="border border-gray-200 px-2 py-2 text-right font-bold text-gray-700">{{ __('reports.pdf_th_net_flow_short') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyFinancialTrend as $row)
                    <tr>
                        <td class="border border-gray-200 px-2 py-2 font-medium">{{ $row['label'] }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['accrual_revenue'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['doctor_share'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['gross_profit'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['expenses_accrual'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['payroll_accrual'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['operating_cost_accrual'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums font-semibold {{ $row['net_profit_accrual'] < 0 ? 'text-red-600' : 'text-teal-700' }}">{{ number_format($row['net_profit_accrual'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['cash_in_patients'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums">{{ number_format($row['cash_paid_out_total'], 2) }}</td>
                        <td class="border border-gray-200 px-2 py-2 tabular-nums font-semibold {{ $row['net_cash_flow'] < 0 ? 'text-red-600' : 'text-sky-800' }}">{{ number_format($row['net_cash_flow'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="m-0 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('invoices.reports_heading_collection_by_method') }}</p>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($incomeByPaymentMethod as $method => $amt)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #0f766e;">
                <p class="m-0 text-xs text-gray-600">{{ \App\Support\PaymentMethods::label((string) $method) }}</p>
                <p class="m-0 mt-1 text-lg font-bold tabular-nums text-[#0F4C81]">{{ number_format($amt, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
            </div>
        @endforeach
    </div>

    <p class="m-0 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.excel_heading_expense_by_pm') }}</p>
    <div class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($expenseOutflowsByPaymentMethod as $method => $amt)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" style="border-top:4px solid #d97706;">
                <p class="m-0 text-xs text-gray-600">{{ \App\Support\PaymentMethods::label((string) $method) }}</p>
                <p class="m-0 mt-1 text-lg font-bold tabular-nums text-amber-900">{{ number_format($amt, 2) }}@if(filled($cur)) <span class="text-xs text-gray-500">{{ $cur }}</span>@endif</p>
            </div>
        @endforeach
    </div>

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('invoices.reports_financial_cards_title') }}</p>
    <div class="mb-10 grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm" style="border-right: 4px solid #10b981;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('invoices.reports_card_paid_full_title') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-emerald-600">{{ number_format($paidAmount, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('invoices.reports_card_paid_full_sub') }}</p>
        </div>
        <div class="rounded-xl border border-amber-100 bg-white p-5 shadow-sm" style="border-right: 4px solid #f59e0b;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('invoices.reports_card_partial_remaining_title') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-amber-600">{{ number_format($partialAmount, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('invoices.reports_card_partial_remaining_sub') }}</p>
        </div>
        <div class="rounded-xl border border-red-100 bg-white p-5 shadow-sm" style="border-right: 4px solid #ef4444;">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('invoices.reports_card_unpaid_title') }}</p>
            <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-red-600">{{ number_format($unpaidAmount, 2) }}@if(filled($cur)) <span class="text-sm text-gray-500 dark:text-[#9CA3AF]">{{ $cur }}</span>@endif</p>
            <p class="m-0 mt-1 text-xs text-gray-400 dark:text-[#94A3B8]">{{ __('invoices.reports_card_unpaid_sub') }}</p>
        </div>
    </div>

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.kpi_heading_general') }}</p>
    <div class="mb-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="m-0 text-sm font-medium text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.excel_row_total_patients') }}</p>
            <p class="m-0 mt-2 text-3xl font-bold tabular-nums text-[#0F4C81]">{{ $totalPatients }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="m-0 text-sm font-medium text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.excel_row_total_doctors') }}</p>
            <p class="m-0 mt-2 text-3xl font-bold tabular-nums text-[#1F7A8C]">{{ $totalDoctors }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="m-0 text-sm font-medium text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.excel_row_total_appointments') }}</p>
            <p class="m-0 mt-2 text-3xl font-bold tabular-nums text-green-700">{{ $totalAppointments }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="m-0 text-sm font-medium text-gray-500 dark:text-[#9CA3AF]">{{ __('visits.report_total_visits_label') }}</p>
            <p class="m-0 mt-2 text-3xl font-bold tabular-nums text-indigo-600">{{ $totalVisits }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="m-0 text-sm font-medium text-gray-500 dark:text-[#9CA3AF]">{{ __('invoices.reports_total_invoices_kpi') }}</p>
            <p class="m-0 mt-2 text-3xl font-bold tabular-nums text-amber-600">{{ $totalInvoices }}</p>
        </div>
    </div>

    @if($hasExpenseFilters)
        <p class="m-0 mb-2 text-sm font-semibold text-gray-800 dark:text-[#F3F4F6]">{{ __('reports.expense_filtered_total_line') }} {{ number_format($expenseFilteredTotal, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
    @endif

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.section_expense_distribution') }}{{ $hasExpenseFilters ? __('reports.expense_distribution_with_filter_suffix') : '' }}</p>
    <div class="mb-10 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[320px] text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_category') }}</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.pdf_th_total_short') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenseSummaryByCategory as $row)
                        <tr class="border-b border-gray-100">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row->category_name }}</td>
                            <td class="px-4 py-3 tabular-nums text-gray-800">{{ number_format((float) $row->total_amount, 2) }}@if(filled($cur)) {{ $cur }}@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-8 text-center text-gray-500">{{ __('reports.empty_no_matching_expenses') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('invoices.reports_heading_status_distribution') }}</p>
    <div class="mb-10 grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-red-100 bg-gradient-to-br from-red-50 to-white p-6 shadow-sm">
            <h3 class="m-0 text-base font-bold text-red-900">{{ __('invoices.reports_status_heading_unpaid') }}</h3>
            <p class="m-0 mt-3 text-4xl font-bold tabular-nums text-red-600">{{ $unpaidInvoices }}</p>
            <p class="m-0 mt-2 text-sm text-red-800">{{ __('invoices.reports_status_amount_prefix') }} {{ number_format($unpaidAmount, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
        </div>
        <div class="rounded-xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm">
            <h3 class="m-0 text-base font-bold text-amber-900">{{ __('invoices.reports_status_heading_partial') }}</h3>
            <p class="m-0 mt-3 text-4xl font-bold tabular-nums text-amber-600">{{ $partialInvoices }}</p>
            <p class="m-0 mt-2 text-sm text-amber-900">{{ __('invoices.reports_status_remaining_prefix') }} {{ number_format($partialAmount, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
            <h3 class="m-0 text-base font-bold text-emerald-900">{{ __('invoices.reports_status_heading_paid') }}</h3>
            <p class="m-0 mt-3 text-4xl font-bold tabular-nums text-emerald-600">{{ $paidInvoices }}</p>
            <p class="m-0 mt-2 text-sm text-emerald-900">{{ __('invoices.reports_status_amount_prefix') }} {{ number_format($paidAmount, 2) }}@if(filled($cur)) {{ $cur }}@endif</p>
        </div>
    </div>

    <p class="m-0 mb-3 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('reports.section_recent_activity') }}</p>
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="bg-[#0F4C81] px-4 py-3 text-white">
                <h3 class="m-0 text-base font-bold">{{ __('reports.recent_patients_heading') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[280px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.recent_patients_th_name') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.recent_patients_th_phone') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.recent_patients_th_registered') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPatients as $patient)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $patient->full_name }}</td>
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

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="bg-[#1F7A8C] px-4 py-3 text-white">
                <h3 class="m-0 text-base font-bold">{{ __('reports.recent_appointments_heading') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[280px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.recent_appointments_th_patient') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.recent_appointments_th_doctor') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('reports.recent_appointments_th_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAppointments as $appointment)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $appointment->patient->full_name ?? __('common.em_dash') }}</td>
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

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="bg-amber-600 px-4 py-3 text-white">
                <h3 class="m-0 text-base font-bold">{{ __('invoices.reports_recent_invoices') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[280px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_invoice_number') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_patient') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $inv)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $inv->invoice_number }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ $inv->patient->full_name ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 font-bold tabular-nums {{ $inv->status == 'paid' ? 'text-emerald-600' : ($inv->status == 'partial' ? 'text-amber-600' : 'text-red-600') }}">{{ number_format((float) $inv->total, 2) }}@if(filled($cur)) <span class="text-gray-500 dark:text-[#9CA3AF] font-medium">{{ $cur }}</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('reports.empty_no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="bg-rose-700 px-4 py-3 text-white">
                <h3 class="m-0 text-base font-bold">{{ __('payments.reports_recent_payments') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[480px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_patient') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('payments.reports_th_invoice') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('common.amount') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('payments.reports_th_method') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('payments.reports_th_payment_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ optional(optional($payment->invoice)->patient)->full_name ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF]">{{ optional($payment->invoice)->invoice_number ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 font-bold tabular-nums text-emerald-600">{{ number_format((float) $payment->amount, 2) }}@if(filled($cur)) <span class="text-gray-500 dark:text-[#9CA3AF] font-medium">{{ $cur }}</span>@endif</td>
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
        <div class="mt-10 rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-5">
            <p class="m-0 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('reports.section_report_footer_heading') }}</p>
            <p class="m-0 mt-2 text-sm leading-relaxed text-gray-700 dark:text-[#E5E7EB] whitespace-pre-wrap">{{ $clinic->report_footer }}</p>
        </div>
    @endif
