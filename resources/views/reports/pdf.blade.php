@php
    $pdfIsRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $clinicLogoPath = ($clinic->clinic_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($clinic->clinic_logo))
        ? str_replace('\\', '/', \Illuminate\Support\Facades\Storage::disk('public')->path($clinic->clinic_logo))
        : null;
    $clinicTitle = $clinic->clinic_name ?: config('app.name', 'Clinic System');
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
    $cur = $clinic->currency;
    $hasExpenseFilters = request()->filled('report_expense_category_id') || request()->filled('report_expense_date_from') || request()->filled('report_expense_date_to');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@pdfStr(__('reports.title'))</title>
    <style>
        @include('partials.pdf-font-rules')
        * { box-sizing: border-box; }
        body {
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        h1, h2, h3, p { margin: 0; }
        .brand { border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 16px; overflow: hidden; }
        .brand .tag { font-size: 9px; font-weight: bold; color: #9ca3af; letter-spacing: 0.05em; text-transform: uppercase; }
        .brand .title { font-size: 20px; font-weight: bold; color: #0F4C81; margin-top: 6px; }
        .brand .sub { font-size: 10px; color: #6b7280; margin-top: 4px; }
        .brand-logo { max-height: 52px; max-width: 130px; }
        .cur { color: #6b7280; font-size: 9px; margin-right: 3px; }
        .report-footer { border: 1px dashed #d1d5db; border-radius: 8px; padding: 12px 14px; margin-top: 16px; text-align: right; background: #f9fafb; }
        .report-footer h3 { font-size: 11px; margin: 0 0 6px; color: #111827; }
        .report-footer p { font-size: 10px; line-height: 1.6; color: #374151; white-space: pre-wrap; margin: 0; }
        .section { margin: 14px 0 8px; font-size: 9px; font-weight: bold; color: #9ca3af; letter-spacing: 0.05em; text-transform: uppercase; text-align: right; }
        .cards { width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 8px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; vertical-align: top; text-align: right; }
        .card p { margin: 0; }
        .card .label { font-size: 10px; color: #4b5563; }
        .card .num { font-size: 16px; font-weight: bold; margin-top: 6px; }
        .card .hint { font-size: 9px; color: #9ca3af; margin-top: 4px; }
        .br-red { border-right: 4px solid #ef4444; }
        .br-amber { border-right: 4px solid #f59e0b; }
        .br-emerald { border-right: 4px solid #10b981; }
        .c-red { color: #dc2626; }
        .c-amber { color: #d97706; }
        .c-emerald { color: #059669; }
        .c-brand { color: #0F4C81; }
        .c-teal { color: #1F7A8C; }
        table.data { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 14px; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: right; }
        table.data th { background: #f9fafb; font-weight: bold; color: #374151; }
        .block-head { color: #fff; padding: 8px 10px; font-size: 11px; font-weight: bold; text-align: right; }
        .bg-navy { background: #0F4C81; }
        .bg-teal { background: #1F7A8C; }
        .bg-amber { background: #d97706; }
        .bg-rose { background: #be123c; }
        .wrap { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:26%; vertical-align:top; text-align:right;">
                    <img src="{{ $clinicLogoPath }}" alt="" class="brand-logo">
                </td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p class="tag">@pdfStr(__('reports.pdf_tag'))</p>
                <h1 class="title">@pdfStr($clinicTitle)</h1>
                <p class="sub">@pdfStr(__('reports.pdf_headline_sub'))</p>
                @if($clinicContact !== '')
                    <p class="sub" style="margin-top:4px;">{{ $clinicContact }}</p>
                @endif
                @if(filled($clinic->clinic_address))
                    <p class="sub" style="margin-top:4px; white-space:pre-wrap;">@pdfStr($clinic->clinic_address)</p>
                @endif
                <p class="sub" style="margin-top:6px;">@pdfStr(__('reports.print_generated_at_label')) {{ $generatedAt->format('d/m/Y H:i') }}</p>
            </td>
        </tr>
    </table>
</div>

@include('reports.partials.pdf-standard-meta')
@include('reports.partials.pdf-unified-summary')

<p class="section">@pdfStr(__('reports.pdf_section_profitability'))</p>
<table class="cards" style="width:100%;">
    <tr>
        <td class="card" style="width:33%; border-right:4px solid #10b981;"><p class="label">@pdfStr(__('reports.accrual_revenue'))</p><p class="num" style="color:#047857;">{{ number_format($totalAccrualRevenue, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:33%; border-right:4px solid #e11d48;"><p class="label">@pdfStr(__('reports.accounts_receivable'))</p><p class="num c-red">{{ number_format($totalAccountsReceivable, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:33%; border-right:4px solid #0891b2;"><p class="label">@pdfStr(__('reports.doctor_share_accrual'))</p><p class="num" style="color:#155e75;">{{ number_format($totalDoctorShareExpense, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
    </tr>
    <tr>
        <td class="card" style="width:33%; border-right:4px solid #6366f1;"><p class="label">@pdfStr(__('reports.kpi_gross_profit'))</p><p class="num" style="color:#3730a3;">{{ number_format($grossProfit, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:33%; border-right:4px solid #f59e0b;"><p class="label">@pdfStr(__('reports.registered_expenses'))</p><p class="num c-amber">{{ number_format($totalExpensesAll, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:33%; border-right:4px solid #7c3aed;"><p class="label">@pdfStr(__('reports.registered_payroll'))</p><p class="num" style="color:#5b21b6;">{{ number_format($totalPayrollPaid, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
    </tr>
    <tr>
        <td class="card" style="width:33%; border-right:4px solid #475569;"><p class="label">@pdfStr(__('reports.kpi_total_operating_cost_accrual'))</p><p class="num" style="color:#334155;">{{ number_format($totalOperatingCost, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:33%; border-right:4px solid {{ $netProfit < 0 ? '#ef4444' : '#0F4C81' }};"><p class="label">@pdfStr(__('reports.net_profit_accrual'))</p><p class="num" style="color:{{ $netProfit < 0 ? '#dc2626' : '#0F4C81' }};">{{ number_format($netProfit, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:33%;"></td>
    </tr>
</table>
<p class="section">@pdfStr(__('reports.pdf_section_cash'))</p>
<table class="cards" style="width:100%;">
    <tr>
        <td class="card" style="width:25%; border-right:4px solid #0d9488;"><p class="label">@pdfStr(__('reports.cash_collection_patients'))</p><p class="num" style="color:#0f766e;">{{ number_format($totalPatientCashIn, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:25%; border-right:4px solid #475569;"><p class="label">@pdfStr(__('reports.pdf_th_out_cash_short'))</p><p class="num" style="color:#334155;">{{ number_format($totalCashOutReport, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:25%; border-right:4px solid #0ea5e9;"><p class="label">@pdfStr(__('reports.kpi_net_cash_flow_cumulative'))</p><p class="num" style="color:{{ $netCashFlowLifetime < 0 ? '#dc2626' : '#0369a1' }};">{{ number_format($netCashFlowLifetime, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:25%; border-right:4px solid #0f766e;"><p class="label">@pdfStr(__('reports.pdf_cash_balance_short'))</p><p class="num" style="color:#0f766e;">{{ number_format($cashBalance, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
    </tr>
</table>

<p class="section">@pdfStr(__('reports.pdf_section_profitability_table'))</p>
<table class="data">
    <thead>
        <tr><th>@pdfStr(__('reports.th_metric'))</th><th>@pdfStr(__('reports.th_today'))</th><th>@pdfStr(__('reports.th_this_month'))</th><th>@pdfStr(__('reports.th_grand_total'))</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>@pdfStr(__('reports.accrual_revenue'))</td>
            <td>{{ number_format($todayAccrualRevenue, 2) }}</td>
            <td>{{ number_format($thisMonthAccrualRevenue, 2) }}</td>
            <td>{{ number_format($totalAccrualRevenue, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.doctor_share_accrual'))</td>
            <td>{{ number_format($todayDoctorShareExpense, 2) }}</td>
            <td>{{ number_format($thisMonthDoctorShareExpense, 2) }}</td>
            <td>{{ number_format($totalDoctorShareExpense, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.registered_expenses'))</td>
            <td>{{ number_format($todayAccrualExpensesRecorded, 2) }}</td>
            <td>{{ number_format($thisMonthAccrualExpensesRecorded, 2) }}</td>
            <td>{{ number_format($totalExpensesAll, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.registered_payroll'))</td>
            <td>{{ number_format($todayAccrualPayrollRecorded, 2) }}</td>
            <td>{{ number_format($thisMonthAccrualPayrollRecorded, 2) }}</td>
            <td>{{ number_format($profitabilitySnapshot['totalSalaries'], 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_gross_profit'))</td>
            <td>{{ number_format($todayGrossProfit, 2) }}</td>
            <td>{{ number_format($thisMonthGrossProfit, 2) }}</td>
            <td>{{ number_format($grossProfit, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_total_operating_cost_accrual'))</td>
            <td>{{ number_format($todayOperatingCostFull, 2) }}</td>
            <td>{{ number_format($thisMonthOperatingCostFull, 2) }}</td>
            <td>{{ number_format($totalOperatingCost, 2) }}</td>
        </tr>
        <tr>
            <td><strong>@pdfStr(__('reports.kpi_net_profit_accrual'))</strong></td>
            <td style="color:{{ $todayNetProfit < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($todayNetProfit, 2) }}</strong></td>
            <td style="color:{{ $thisMonthProfit < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($thisMonthProfit, 2) }}</strong></td>
            <td style="color:{{ $netProfit < 0 ? '#dc2626' : '#0F4C81' }};"><strong>{{ number_format($netProfit, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<p class="section">@pdfStr(__('reports.pdf_section_cash_table'))</p>
<table class="data">
    <thead>
        <tr><th>@pdfStr(__('reports.th_metric'))</th><th>@pdfStr(__('reports.th_today'))</th><th>@pdfStr(__('reports.th_this_month'))</th><th>@pdfStr(__('reports.th_grand_total'))</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>@pdfStr(__('reports.row_cash_patients_detail'))</td>
            <td>{{ number_format($todayCashCollected, 2) }}</td>
            <td>{{ number_format($thisMonthCashCollected, 2) }}</td>
            <td>{{ number_format($totalPatientCashIn, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('payroll.reports_table_row_expense_cash_out'))</td>
            <td>{{ number_format($todayExpenses, 2) }}</td>
            <td>{{ number_format($thisMonthExpenses, 2) }}</td>
            <td>{{ number_format($cashFlowSnapshot['cashOutExpensesTotal'], 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('payroll.reports_table_row_payroll_cash_out'))</td>
            <td>{{ number_format($todayPayroll, 2) }}</td>
            <td>{{ number_format($thisMonthPayroll, 2) }}</td>
            <td>{{ number_format($cashFlowSnapshot['cashOutPayrollTotal'], 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.row_doctor_payouts_cash'))</td>
            <td>{{ number_format($todayDoctorPayoutsCash, 2) }}</td>
            <td>{{ number_format($thisMonthDoctorPayoutsCash, 2) }}</td>
            <td>{{ number_format($cashFlowSnapshot['cashOutDoctorsTotal'], 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_total_cash_out'))</td>
            <td>{{ number_format($todayCashPaidOutReport, 2) }}</td>
            <td>{{ number_format($thisMonthCashPaidOutReport, 2) }}</td>
            <td>{{ number_format($totalCashOutReport, 2) }}</td>
        </tr>
        <tr>
            <td><strong>@pdfStr(__('reports.kpi_net_cash_flow_cumulative'))</strong></td>
            <td style="color:{{ $todayNetCashFlowReport < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($todayNetCashFlowReport, 2) }}</strong></td>
            <td style="color:{{ $thisMonthNetCashFlowReport < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($thisMonthNetCashFlowReport, 2) }}</strong></td>
            <td style="color:{{ $netCashFlowLifetime < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($netCashFlowLifetime, 2) }}</strong></td>
        </tr>
        <tr>
            <td><strong>@pdfStr(__('reports.kpi_cash_balance'))</strong></td>
            <td>@pdfStr(__('common.em_dash'))</td>
            <td>@pdfStr(__('common.em_dash'))</td>
            <td><strong>{{ number_format($cashBalance, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<p class="section">@pdfStr(__('reports.pdf_section_extended_cards'))</p>
<table class="cards" style="width:100%;">
    <tr>
        <td class="card" style="width:25%; border-right:4px solid #475569;"><p class="label">@pdfStr(__('reports.pdf_cash_all_time_out'))</p><p class="num" style="color:#334155;">{{ number_format($totalOperatingOutflows, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card br-red" style="width:25%;"><p class="label">@pdfStr(__('reports.pdf_receivable_due'))</p><p class="num c-red">{{ number_format($totalReceivableExposure, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:25%; border-right:4px solid #0ea5e9;"><p class="label">@pdfStr(__('reports.pdf_ytd_net_profit'))</p><p class="num" style="color:{{ $ytdNetProfit < 0 ? '#dc2626' : '#0369a1' }};">{{ number_format($ytdNetProfit, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
        <td class="card" style="width:25%; border-right:4px solid #0f766e;"><p class="label">@pdfStr(__('reports.pdf_cash_balance_short'))</p><p class="num" style="color:#0f766e;">{{ number_format($cashBalance, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p></td>
    </tr>
</table>

<p class="section">@pdfStr(__('reports.section_period_comparison'))</p>
<table class="data">
    <thead>
        <tr><th>@pdfStr(__('reports.th_metric'))</th><th>@pdfStr(__('reports.th_last_month'))</th><th>@pdfStr(__('reports.th_this_month'))</th><th>@pdfStr(__('reports.th_ytd'))</th><th>@pdfStr(__('reports.th_last_30_days'))</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>@pdfStr(__('reports.accrual_revenue'))</td>
            <td>{{ number_format($lastMonthAccrualRevenue, 2) }}</td>
            <td>{{ number_format($thisMonthAccrualRevenue, 2) }}</td>
            <td>{{ number_format($ytdAccrualRevenue, 2) }}</td>
            <td>{{ number_format($last30AccrualRevenue, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_patient_cash_in'))</td>
            <td>{{ number_format($lastMonthCashCollected, 2) }}</td>
            <td>{{ number_format($thisMonthCashCollected, 2) }}</td>
            <td>{{ number_format($ytdCashCollected, 2) }}</td>
            <td>{{ number_format($last30CashCollected, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.doctor_share_accrual'))</td>
            <td>{{ number_format($lastMonthDoctorShareExpense, 2) }}</td>
            <td>{{ number_format($thisMonthDoctorShareExpense, 2) }}</td>
            <td>{{ number_format($ytdDoctorShareExpense, 2) }}</td>
            <td>{{ number_format($last30DoctorShareExpense, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('payroll.reports_table_row_accrued_expenses'))</td>
            <td>{{ number_format($lastMonthAccrualExpenses, 2) }}</td>
            <td>{{ number_format($thisMonthAccrualExpensesRecorded, 2) }}</td>
            <td>{{ number_format($ytdAccrualExpenses, 2) }}</td>
            <td>{{ number_format($last30AccrualExpenses, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('payroll.reports_table_row_accrued_payroll'))</td>
            <td>{{ number_format($lastMonthAccrualPayroll, 2) }}</td>
            <td>{{ number_format($thisMonthAccrualPayrollRecorded, 2) }}</td>
            <td>{{ number_format($ytdAccrualPayroll, 2) }}</td>
            <td>{{ number_format($last30AccrualPayroll, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('payroll.reports_table_row_expense_cash_out'))</td>
            <td>{{ number_format($lastMonthExpenses, 2) }}</td>
            <td>{{ number_format($thisMonthExpenses, 2) }}</td>
            <td>{{ number_format($ytdExpenses, 2) }}</td>
            <td>{{ number_format($last30Expenses, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('payroll.reports_table_row_payroll_cash_out'))</td>
            <td>{{ number_format($lastMonthPayroll, 2) }}</td>
            <td>{{ number_format($thisMonthPayroll, 2) }}</td>
            <td>{{ number_format($ytdPayroll, 2) }}</td>
            <td>{{ number_format($last30Payroll, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.row_doctor_payouts_cash'))</td>
            <td>{{ number_format($lastMonthDoctorPayoutsCash, 2) }}</td>
            <td>{{ number_format($thisMonthDoctorPayoutsCash, 2) }}</td>
            <td>{{ number_format($ytdDoctorPayoutsCash, 2) }}</td>
            <td>{{ number_format($last30DoctorPayoutsCash, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_gross_profit'))</td>
            <td>{{ number_format($lastMonthGrossProfit, 2) }}</td>
            <td>{{ number_format($thisMonthGrossProfit, 2) }}</td>
            <td>{{ number_format($ytdGrossProfit, 2) }}</td>
            <td>{{ number_format($last30GrossProfit, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_total_operating_cost_accrual'))</td>
            <td>{{ number_format($lastMonthOperatingCostFull, 2) }}</td>
            <td>{{ number_format($thisMonthOperatingCostFull, 2) }}</td>
            <td>{{ number_format($ytdOperatingCostFull, 2) }}</td>
            <td>{{ number_format($last30OperatingCostFull, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_total_cash_out'))</td>
            <td>{{ number_format($lastMonthCashOut, 2) }}</td>
            <td>{{ number_format($thisMonthCashPaidOutReport, 2) }}</td>
            <td>{{ number_format($ytdCashOut, 2) }}</td>
            <td>{{ number_format($last30CashOut, 2) }}</td>
        </tr>
        <tr>
            <td>@pdfStr(__('reports.kpi_net_cash_flow_cumulative'))</td>
            <td>{{ number_format($lastMonthNetCashFlow, 2) }}</td>
            <td>{{ number_format($thisMonthNetCashFlowReport, 2) }}</td>
            <td>{{ number_format($ytdNetCashFlow, 2) }}</td>
            <td>{{ number_format($last30NetCashFlow, 2) }}</td>
        </tr>
        <tr>
            <td><strong>@pdfStr(__('reports.kpi_net_profit_accrual'))</strong></td>
            <td style="color:{{ $lastMonthProfit < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($lastMonthProfit, 2) }}</strong></td>
            <td style="color:{{ $thisMonthProfit < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($thisMonthProfit, 2) }}</strong></td>
            <td style="color:{{ $ytdNetProfit < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($ytdNetProfit, 2) }}</strong></td>
            <td style="color:{{ $last30NetProfit < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($last30NetProfit, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<p class="section">@pdfStr(__('reports.section_monthly_trend'))</p>
<table class="data" style="font-size:8px;">
    <thead>
        <tr><th>@pdfStr(__('reports.pdf_th_month'))</th><th>@pdfStr(__('reports.print_short_accrual_revenue'))</th><th>@pdfStr(__('reports.kpi_doctor_share_table_short'))</th><th>@pdfStr(__('reports.kpi_gross_profit'))</th><th>@pdfStr(__('expenses.reports_th_expenses_accrual'))</th><th>@pdfStr(__('payroll.reports_th_payroll_accrual'))</th><th>@pdfStr(__('reports.pdf_th_operation_accrual'))</th><th>@pdfStr(__('reports.kpi_net_profit_accrual'))</th><th>@pdfStr(__('reports.pdf_th_collect_cash_short'))</th><th>@pdfStr(__('reports.pdf_th_out_cash_short'))</th><th>@pdfStr(__('reports.pdf_th_net_flow_short'))</th></tr>
    </thead>
    <tbody>
        @foreach($monthlyFinancialTrend as $row)
            <tr>
                <td>@pdfStr($row['label'])</td>
                <td>{{ number_format($row['accrual_revenue'], 2) }}</td>
                <td>{{ number_format($row['doctor_share'], 2) }}</td>
                <td>{{ number_format($row['gross_profit'], 2) }}</td>
                <td>{{ number_format($row['expenses_accrual'], 2) }}</td>
                <td>{{ number_format($row['payroll_accrual'], 2) }}</td>
                <td>{{ number_format($row['operating_cost_accrual'], 2) }}</td>
                <td style="color:{{ $row['net_profit_accrual'] < 0 ? '#dc2626' : '#0f766e' }};"><strong>{{ number_format($row['net_profit_accrual'], 2) }}</strong></td>
                <td>{{ number_format($row['cash_in_patients'], 2) }}</td>
                <td>{{ number_format($row['cash_paid_out_total'], 2) }}</td>
                <td style="color:{{ $row['net_cash_flow'] < 0 ? '#dc2626' : '#0369a1' }};"><strong>{{ number_format($row['net_cash_flow'], 2) }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="section">@pdfStr(__('reports.excel_heading_income_by_pm'))</p>
<table class="data">
    <thead><tr><th>@pdfStr(__('reports.pdf_th_method'))</th><th>@pdfStr(__('reports.pdf_th_amount'))</th></tr></thead>
    <tbody>
        @foreach($incomeByPaymentMethod as $method => $amt)
            <tr>
                <td>@pdfStr(\App\Support\PaymentMethods::label($method))</td>
                <td>{{ number_format($amt, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="section">@pdfStr(__('expenses.reports_heading_expense_payments_by_method'))</p>
<table class="data">
    <thead><tr><th>@pdfStr(__('reports.pdf_th_method'))</th><th>@pdfStr(__('reports.pdf_th_amount'))</th></tr></thead>
    <tbody>
        @foreach($expenseOutflowsByPaymentMethod as $method => $amt)
            <tr>
                <td>@pdfStr(\App\Support\PaymentMethods::label($method))</td>
                <td>{{ number_format($amt, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="section">@pdfStr(__('reports.pdf_invoice_cards_title'))</p>
<table class="cards" style="width:100%;">
    <tr>
        <td class="card br-red" style="width:33%;">
            <p class="label">@pdfStr(__('reports.pdf_invoice_unpaid_total'))</p>
            <p class="num c-red">{{ number_format($unpaidAmount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
            <p class="hint">@pdfStr(__('reports.pdf_invoice_unpaid_hint'))</p>
        </td>
        <td class="card br-amber" style="width:33%;">
            <p class="label">@pdfStr(__('reports.pdf_invoice_partial_remaining'))</p>
            <p class="num c-amber">{{ number_format($partialAmount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
            <p class="hint">@pdfStr(__('reports.pdf_invoice_partial_formula_hint'))</p>
        </td>
        <td class="card br-emerald" style="width:33%;">
            <p class="label">@pdfStr(__('reports.pdf_invoice_paid_full'))</p>
            <p class="num c-emerald">{{ number_format($paidAmount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
            <p class="hint">@pdfStr(__('reports.pdf_invoice_paid_hint'))</p>
        </td>
    </tr>
</table>

<p class="section">@pdfStr(__('reports.kpi_heading_general'))</p>
<table class="cards" style="width:100%;">
    <tr>
        <td class="card" style="width:20%;"><p class="label">@pdfStr(__('reports.excel_row_total_patients'))</p><p class="num c-brand">{{ $totalPatients }}</p></td>
        <td class="card" style="width:20%;"><p class="label">@pdfStr(__('reports.excel_row_total_doctors'))</p><p class="num c-teal">{{ $totalDoctors }}</p></td>
        <td class="card" style="width:20%;"><p class="label">@pdfStr(__('reports.excel_row_total_appointments'))</p><p class="num" style="color:#15803d;">{{ $totalAppointments }}</p></td>
        <td class="card" style="width:20%;"><p class="label">@pdfStr(__('visits.report_total_visits_label'))</p><p class="num" style="color:#4f46e5;">{{ $totalVisits }}</p></td>
        <td class="card" style="width:20%;"><p class="label">@pdfStr(__('reports.excel_row_total_invoices'))</p><p class="num c-amber">{{ $totalInvoices }}</p></td>
    </tr>
</table>

@if($hasExpenseFilters)
    <p class="sub" style="margin:10px 0 6px; font-weight:bold;">@pdfStr(__('expenses.reports_filtered_total_prefix')) {{ number_format($expenseFilteredTotal, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
@endif

<p class="section">@pdfStr(__('expenses.reports_distribution_by_category'))@if($hasExpenseFilters)@pdfStr(__('expenses.reports_distribution_with_filter_suffix'))@endif</p>
<div class="wrap">
    <table class="data">
        <thead>
            <tr>
                <th>@pdfStr(__('expenses.field_category'))</th>
                <th>@pdfStr(__('reports.pdf_th_total_short'))</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenseSummaryByCategory as $row)
                <tr>
                    <td>@pdfStr($row->category_name)</td>
                    <td>{{ number_format((float) $row->total_amount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="2" style="text-align:center;color:#6b7280;">@pdfStr(__('expenses.reports_no_expenses_matching'))</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="section">@pdfStr(__('reports.pdf_section_invoice_status'))</p>
<table class="cards" style="width:100%;">
    <tr>
        <td class="card" style="width:33%; background:#fef2f2;">
            <h3 style="font-size:11px;color:#7f1d1d;">@pdfStr(__('invoices.reports_status_heading_unpaid'))</h3>
            <p class="num c-red" style="font-size:22px;">{{ $unpaidInvoices }}</p>
            <p class="hint" style="color:#991b1b;">@pdfStr(__('invoices.reports_status_amount_prefix')) {{ number_format($unpaidAmount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
        </td>
        <td class="card" style="width:33%; background:#fffbeb;">
            <h3 style="font-size:11px;color:#78350f;">@pdfStr(__('invoices.reports_status_heading_partial'))</h3>
            <p class="num c-amber" style="font-size:22px;">{{ $partialInvoices }}</p>
            <p class="hint" style="color:#92400e;">@pdfStr(__('invoices.reports_status_remaining_prefix')) {{ number_format($partialAmount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
        </td>
        <td class="card" style="width:33%; background:#ecfdf5;">
            <h3 style="font-size:11px;color:#065f46;">@pdfStr(__('invoices.reports_status_heading_paid'))</h3>
            <p class="num c-emerald" style="font-size:22px;">{{ $paidInvoices }}</p>
            <p class="hint" style="color:#047857;">@pdfStr(__('invoices.reports_status_amount_prefix')) {{ number_format($paidAmount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
        </td>
    </tr>
</table>

<p class="section">@pdfStr(__('reports.section_recent_activity'))</p>

<div class="wrap">
    <div class="block-head bg-navy">@pdfStr(__('reports.recent_patients_heading'))</div>
    <table class="data">
        <thead>
            <tr>
                <th>@pdfStr(__('reports.recent_patients_th_name'))</th>
                <th>@pdfStr(__('reports.recent_patients_th_phone'))</th>
                <th>@pdfStr(__('reports.recent_patients_th_registered'))</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPatients as $patient)
                <tr>
                    <td>@pdfStr($patient->full_name)</td>
                    <td>{{ $patient->phone ?? __('common.em_dash') }}</td>
                    <td>@safeDate($patient->created_at)</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#6b7280;">@pdfStr(__('reports.empty_no_data'))</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="wrap">
    <div class="block-head bg-teal">@pdfStr(__('reports.recent_appointments_heading'))</div>
    <table class="data">
        <thead>
            <tr>
                <th>@pdfStr(__('reports.recent_appointments_th_patient'))</th>
                <th>@pdfStr(__('reports.recent_appointments_th_doctor'))</th>
                <th>@pdfStr(__('reports.recent_appointments_th_date'))</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAppointments as $appointment)
                <tr>
                    <td>@pdfStr($appointment->patient->full_name ?? __('common.em_dash'))</td>
                    <td>@pdfStr($appointment->doctor->full_name ?? __('common.em_dash'))</td>
                    <td>@safeDate($appointment->appointment_date)</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#6b7280;">@pdfStr(__('reports.empty_no_data'))</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="wrap">
    <div class="block-head bg-amber">@pdfStr(__('invoices.reports_recent_invoices'))</div>
    <table class="data">
        <thead>
            <tr>
                <th>@pdfStr(__('invoices.field_invoice_number'))</th>
                <th>@pdfStr(__('invoices.field_patient'))</th>
                <th>@pdfStr(__('invoices.field_total'))</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentInvoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>@pdfStr($inv->patient->full_name ?? __('common.em_dash'))</td>
                    <td>{{ number_format((float) $inv->total, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#6b7280;">@pdfStr(__('reports.empty_no_data'))</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="wrap">
    <div class="block-head bg-rose">@pdfStr(__('reports.pdf_recent_five_payments'))</div>
    <table class="data">
        <thead>
            <tr>
                <th>@pdfStr(__('invoices.field_patient'))</th>
                <th>@pdfStr(__('payments.reports_th_invoice'))</th>
                <th>@pdfStr(__('common.amount'))</th>
                <th>@pdfStr(__('payments.reports_th_method'))</th>
                <th>@pdfStr(__('payments.reports_th_payment_date'))</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPayments as $payment)
                <tr>
                    <td>@pdfStr(optional(optional($payment->invoice)->patient)->full_name ?? __('common.em_dash'))</td>
                    <td>{{ optional($payment->invoice)->invoice_number ?? __('common.em_dash') }}</td>
                    <td>{{ number_format((float) $payment->amount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</td>
                    <td>@pdfStr(\App\Support\PaymentMethods::label((string) ($payment->payment_method ?? '')))</td>
                    <td>@safeDate($payment->payment_date)</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280;">@pdfStr(__('reports.empty_no_data'))</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(filled($clinic->report_footer))
    <div class="report-footer">
        <h3>@pdfStr(__('reports.section_report_footer_heading'))</h3>
        <p>@pdfStr($clinic->report_footer)</p>
    </div>
@endif
</body>
</html>
