@php
    $clinicLogoPath = ($clinic->clinic_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($clinic->clinic_logo))
        ? str_replace('\\', '/', \Illuminate\Support\Facades\Storage::disk('public')->path($clinic->clinic_logo))
        : null;
    $clinicTitle = $clinic->clinic_name ?: config('app.name', 'Clinic System');
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>receivables</title>
    <style>
        @include('partials.pdf-font-rules')
        * { box-sizing: border-box; }
        body, table, th, td { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        .brand { border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 14px; }
        .title { font-size: 18px; font-weight: bold; color: #0F4C81; margin: 0; }
        .sub { font-size: 10px; color: #6b7280; margin: 4px 0 0; }
        .brand-logo { max-height: 48px; max-width: 120px; }
        .section { margin: 12px 0 6px; font-size: 9px; font-weight: bold; color: #9ca3af; text-align: right; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 5px 6px; text-align: right; }
        table.data th { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
<div class="brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:24%; vertical-align:top; text-align:right;"><img src="{{ $clinicLogoPath }}" alt="" class="brand-logo"></td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p class="sub" style="font-weight:bold; color:#9ca3af;">@pdfStr(__('invoices.receivables_nav_reports'))</p>
                <h1 class="title">@pdfStr($clinicTitle)</h1>
                <p class="sub">@pdfStr(__('invoices.receivables_meta_title'))</p>
                @if($clinicContact !== '')
                    <p class="sub">{{ $clinicContact }}</p>
                @endif
                <p class="sub">@pdfStr(__('invoices.receivables_pdf_print_date', ['datetime' => $generatedAt->format('d/m/Y H:i')]))</p>
            </td>
        </tr>
    </table>
</div>
@include('reports.partials.pdf-standard-meta')
@include('reports.partials.pdf-unified-summary')

<p class="section">@pdfStr(__('invoices.receivables_pdf_total_remaining_filtered'))</p>
<p style="font-size:14px;font-weight:bold;color:#047857;">{{ number_format((float) $totalReceivables, 2) }} {{ $cur }}</p>

<p class="section">@pdfStr(__('invoices.receivables_pdf_section_table'))</p>
<table class="data">
    <thead>
    <tr>
        <th>@pdfStr(__('invoices.field_patient'))</th>
        <th>@pdfStr(__('invoices.field_invoice_number'))</th>
        <th>@pdfStr(__('invoices.field_doctor'))</th>
        <th>@pdfStr(__('invoices.field_total'))</th>
        <th>@pdfStr(__('invoices.field_paid'))</th>
        <th>@pdfStr(__('invoices.field_remaining'))</th>
        <th>@pdfStr(__('invoices.field_status'))</th>
        <th>@pdfStr(__('invoices.field_due_date_column'))</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
        @php($rem = round((float) $inv->total - (float) $inv->paid, 2))
        <tr>
            <td>@pdfStr(optional($inv->patient)->full_name ?? __('common.em_dash'))</td>
            <td>{{ $inv->invoice_number }}</td>
            <td>@pdfStr(optional($inv->treatingDoctor)->full_name ?? __('common.em_dash'))</td>
            <td>{{ number_format((float) $inv->total, 2) }}</td>
            <td>{{ number_format((float) $inv->paid, 2) }}</td>
            <td>{{ number_format($rem, 2) }}</td>
            <td>@pdfStr($inv->status === 'partial' ? __('common.partial') : __('common.unpaid'))</td>
            <td>{{ $inv->due_date?->format('d/m/Y') ?? __('common.em_dash') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
