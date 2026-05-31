@php
    $t = $statementTotals ?? ['total_invoiced' => 0, 'total_paid' => 0, 'ending_balance' => 0];
@endphp
<p class="section">@pdfStr(__('patients.pdf_summary_title'))</p>
<table class="data">
    <thead>
    <tr>
        <th>@pdfStr(__('patients.excel_header_item'))</th>
        <th>@pdfStr(__('patients.excel_header_value'))</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>@pdfStr(__('patients.pdf_summary_row_total_invoiced'))</td>
        <td>{{ number_format((float) ($t['total_invoiced'] ?? 0), 2) }}@if(filled($cur ?? '')) {{ $cur }}@endif</td>
    </tr>
    <tr>
        <td>@pdfStr(__('patients.pdf_summary_row_total_paid'))</td>
        <td>{{ number_format((float) ($t['total_paid'] ?? 0), 2) }}@if(filled($cur ?? '')) {{ $cur }}@endif</td>
    </tr>
    <tr>
        <td>@pdfStr(__('patients.pdf_summary_row_balance'))</td>
        <td>{{ number_format((float) ($t['ending_balance'] ?? 0), 2) }}@if(filled($cur ?? '')) {{ $cur }}@endif</td>
    </tr>
    </tbody>
</table>
