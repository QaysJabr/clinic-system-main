@php
    $cur = $clinic->currency;
    $paid = $expense->paidTotal();
    $remaining = $expense->remainingAmount();
    $statusRaw = $remaining <= 0.009 ? __('expenses.status_full_settled') : ($paid > 0.009 ? __('expenses.status_partial_settled') : __('expenses.status_unpaid'));
    $clinicLogoPath = ($clinic->clinic_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($clinic->clinic_logo))
        ? str_replace('\\', '/', \Illuminate\Support\Facades\Storage::disk('public')->path($clinic->clinic_logo))
        : null;
    $clinicTitle = $clinic->clinic_name ?: config('app.name', __('expenses.print_fallback_clinic'));
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@pdfStr($expense->documentNumber())</title>
    <style>
        @include('partials.pdf-font-rules')
        * { box-sizing: border-box; }
        body {
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 24px;
            background: #fff;
        }
        h1, h2, p { margin: 0; }
        .brand { border-bottom: 1px solid #e5e7eb; padding-bottom: 16px; margin-bottom: 20px; text-align: right; overflow: hidden; }
        .brand .tag { font-size: 10px; font-weight: bold; color: #9ca3af; letter-spacing: 0.05em; text-transform: uppercase; }
        .brand .title { font-size: 22px; font-weight: bold; color: #0F4C81; margin-top: 6px; }
        .brand .sub { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .brand-logo { max-height: 56px; max-width: 140px; }
        .cur { color: #6b7280; font-size: 10px; margin-right: 4px; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; text-align: right; }
        .box h2 { font-size: 13px; font-weight: bold; margin-bottom: 10px; color: #111827; }
        .row { display: table; width: 100%; font-size: 11px; padding: 4px 0; }
        .row dt { display: table-cell; color: #6b7280; width: 40%; text-align: right; }
        .row dd { display: table-cell; font-weight: 600; color: #111827; text-align: right; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: right; }
        th { background: #f9fafb; font-weight: bold; color: #374151; }
        .totals { border: 1px solid rgba(15, 76, 129, 0.25); background: #F7F9FC; border-radius: 8px; padding: 14px 16px; text-align: right; }
        .totals .trow { overflow: hidden; padding: 6px 0; font-size: 12px; }
        .totals .trow strong { float: right; color: #4b5563; clear: right; }
        .totals .trow span { float: left; font-weight: bold; }
        .totals .big { font-size: 15px; color: #0F4C81; }
        h2.section { font-size: 13px; margin-bottom: 8px; text-align: right; }
        .notes { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; margin-top: 16px; text-align: right; }
        .notes h2 { font-size: 13px; margin-bottom: 8px; }
        .notes p { font-size: 11px; line-height: 1.6; color: #374151; white-space: pre-wrap; }
    </style>
</head>
<body>

<div class="brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:28%; vertical-align:top; text-align:right;">
                    <img src="{{ $clinicLogoPath }}" alt="" class="brand-logo">
                </td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p class="tag">@pdfStr(__('expenses.document_tag'))</p>
                <h1 class="title">@pdfStr($clinicTitle)</h1>
                @if($clinicContact !== '')
                    <p class="sub">{{ $clinicContact }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <h2>@pdfStr(__('expenses.section_details'))</h2>
    <div class="row"><dt>@pdfStr(__('expenses.field_document_number'))</dt><dd>{{ $expense->documentNumber() }}</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_expense_title_label'))</dt><dd>@pdfStr($expense->title)</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_category'))</dt><dd>@pdfStr(optional($expense->category)->name ?? __('common.em_dash'))</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_settlement_type'))</dt><dd>@pdfStr($expense->isInstallments() ? __('expenses.settlement_installments_word') : __('expenses.settlement_single_word'))</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_commitment_date_print'))</dt><dd>@safeDate($expense->expense_date)</dd></div>
    <div class="row"><dt>@pdfStr(__('common.status'))</dt><dd>@pdfStr($statusRaw)</dd></div>
</div>

<h2 class="section">@pdfStr(__('expenses.section_payments'))</h2>
<table>
    <thead>
        <tr>
            <th>@pdfStr(__('expenses.field_amount_currency'))</th>
            <th>@pdfStr(__('expenses.field_pay_date'))</th>
            <th>@pdfStr(__('expenses.th_payment_method'))</th>
            <th>@pdfStr(__('expenses.th_notes'))</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expense->paymentsOrdered as $p)
            <tr>
                <td>{{ number_format((float) $p->amount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</td>
                <td>@safeDate($p->paid_at)</td>
                <td>@pdfStr(\App\Support\PaymentMethods::label($p->payment_method))</td>
                <td>@pdfStr($p->notes ?? __('common.em_dash'))</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <div class="trow"><strong>@pdfStr(__('expenses.section_summary_total'))</strong><span class="big">{{ number_format((float) $expense->amount, 2) }}@if(filled($cur))<span class="cur" style="font-size:13px;">{{ $cur }}</span>@endif</span></div>
    <div class="trow"><strong>@pdfStr(__('expenses.section_summary_paid'))</strong><span style="color:#047857;">{{ number_format($paid, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</span></div>
    <div class="trow" style="border-top:1px solid #e5e7eb;padding-top:10px;margin-top:6px;"><strong>@pdfStr(__('expenses.section_summary_remaining'))</strong><span style="color:#b45309;">{{ number_format($remaining, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</span></div>
</div>

@if($expense->notes)
    <div class="notes">
        <h2>@pdfStr(__('expenses.th_notes'))</h2>
        <p>@pdfStr($expense->notes)</p>
    </div>
@endif
</body>
</html>
