@php($cur = $clinic->currency ?? '')
<p class="section doc-section">@pdfStr(__('reports.title')) @pdfStr(__('common.em_dash')) @pdfStr(__('common.summary'))</p>
<table class="data doc-table">
    <thead>
    <tr>
        <th>@pdfStr(__('common.description'))</th>
        <th>@pdfStr(__('common.amount'))</th>
    </tr>
    </thead>
    <tbody>
    <tr><td>@pdfStr(__('reports.accrual_revenue'))</td><td>{{ number_format((float) ($totalAccrualRevenue ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    <tr><td>@pdfStr(__('reports.cash_collection_patients'))</td><td>{{ number_format((float) ($totalPatientCashIn ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    <tr><td>@pdfStr(__('reports.accounts_receivable'))</td><td>{{ number_format((float) ($totalAccountsReceivable ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    <tr><td>@pdfStr(__('reports.doctor_share_accrual'))</td><td>{{ number_format((float) ($totalDoctorShareExpense ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    <tr><td>@pdfStr(__('reports.registered_expenses'))</td><td>{{ number_format((float) ($totalExpensesAll ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    <tr><td>@pdfStr(__('reports.registered_payroll'))</td><td>{{ number_format((float) ($totalPayrollPaid ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    <tr><td>@pdfStr(__('reports.net_profit_accrual'))</td><td>{{ number_format((float) ($netProfit ?? 0), 2) }}@if(filled($cur)) {{ $cur }}@endif</td></tr>
    </tbody>
</table>
