@php
    $cur = $clinic->currency;
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
    <title>@pdfStr(__('expenses.pdf_html_title_payment'))</title>
    <style>
        @include('partials.pdf-font-rules')
        * { box-sizing: border-box; }
        body { font-size: 12px; color: #111827; margin: 0; padding: 24px; background: #fff; }
        body, table, th, td, div, p { font-family: 'DejaVu Sans', sans-serif; }
        .brand { border-bottom: 1px solid #e5e7eb; padding-bottom: 16px; margin-bottom: 20px; text-align: right; }
        .brand .title { font-size: 22px; font-weight: bold; color: #0F4C81; margin-top: 6px; }
        .brand-logo { max-height: 56px; max-width: 140px; }
        .cur { color: #6b7280; font-size: 10px; margin-right: 4px; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; margin-bottom: 12px; text-align: right; }
        .row { display: table; width: 100%; font-size: 11px; padding: 4px 0; }
        .row dt { display: table-cell; color: #6b7280; width: 38%; text-align: right; }
        .row dd { display: table-cell; font-weight: 600; color: #111827; text-align: right; }
        .highlight { border: 1px solid rgba(15, 76, 129, 0.25); background: #F7F9FC; border-radius: 8px; padding: 16px; text-align: right; }
        .big { font-size: 20px; font-weight: bold; color: #0F4C81; }
    </style>
</head>
<body>
<div class="brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:28%; vertical-align:top; text-align:right;"><img src="{{ $clinicLogoPath }}" alt="" class="brand-logo"></td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p style="font-size:10px;font-weight:bold;color:#9ca3af;margin:0;">@pdfStr(__('expenses.pdf_tag_expense_payment'))</p>
                <h1 class="title">@pdfStr($clinicTitle)</h1>
                @if($clinicContact !== '')<p style="font-size:11px;color:#6b7280;margin-top:4px;">{{ $clinicContact }}</p>@endif
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <div class="row"><dt>@pdfStr(__('expenses.field_expense_document'))</dt><dd>{{ $expense->documentNumber() }}</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_expense_title_label'))</dt><dd>@pdfStr($expense->title)</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_category'))</dt><dd>@pdfStr(optional($expense->category)->name ?? __('common.em_dash'))</dd></div>
</div>

<div class="highlight">
    <p style="font-size:11px;color:#4b5563;margin:0 0 8px;">@pdfStr(__('expenses.pdf_section_amount_paid'))</p>
    <p class="big">{{ number_format((float) $payment->amount, 2) }}@if(filled($cur))<span class="cur">{{ $cur }}</span>@endif</p>
    <div class="row" style="margin-top:12px;"><dt>@pdfStr(__('expenses.field_pay_date'))</dt><dd>@safeDate($payment->paid_at)</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.th_payment_method'))</dt><dd>@pdfStr(\App\Support\PaymentMethods::label($payment->payment_method))</dd></div>
    <div class="row"><dt>@pdfStr(__('expenses.field_recorded_by'))</dt><dd>@pdfStr(optional($payment->creator)->name ?? __('common.em_dash'))</dd></div>
    @if($payment->notes)
        <div class="row"><dt>@pdfStr(__('expenses.th_notes'))</dt><dd>@pdfStr($payment->notes)</dd></div>
    @endif
</div>
</body>
</html>
