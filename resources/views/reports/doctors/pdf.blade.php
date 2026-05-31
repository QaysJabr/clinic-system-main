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
    <title>doctor-report</title>
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
                <p class="sub" style="font-weight:bold; color:#9ca3af;">@pdfStr(__('doctors.pdf_tag'))</p>
                <h1 class="title">@pdfStr($clinicTitle)</h1>
                <p class="sub">@pdfStr(__('doctors.pdf_doctor_report_label')): @pdfStr($doctor->full_name)</p>
                @if($clinicContact !== '')
                    <p class="sub">{{ $clinicContact }}</p>
                @endif
                <p class="sub">@pdfStr(__('patients.pdf_printed_at_line', ['datetime' => $generatedAt->format('d/m/Y H:i')]))</p>
            </td>
        </tr>
    </table>
</div>
@include('reports.partials.pdf-standard-meta')
@include('reports.partials.pdf-unified-summary')

<p class="section">@pdfStr(__('doctors.pdf_section_doctor_summary'))</p>
<table class="data">
    <tbody>
    <tr><th>@pdfStr(__('doctors.pdf_stat_invoice_count'))</th><td>{{ $stats['invoice_count'] }}</td></tr>
    <tr><th>@pdfStr(__('doctors.pdf_stat_accrual'))</th><td>{{ number_format($stats['accrual_revenue'], 2) }} {{ $cur }}</td></tr>
    <tr><th>@pdfStr(__('doctors.pdf_stat_doctor_share'))</th><td>{{ number_format($stats['doctor_share_total'], 2) }} {{ $cur }}</td></tr>
    <tr><th>@pdfStr(__('doctors.pdf_stat_pending'))</th><td>{{ number_format($stats['earnings_pending'], 2) }} {{ $cur }}</td></tr>
    <tr><th>@pdfStr(__('doctors.pdf_stat_paid'))</th><td>{{ number_format($stats['earnings_paid'], 2) }} {{ $cur }}</td></tr>
    </tbody>
</table>

<p class="section">@pdfStr(__('doctors.pdf_section_invoices'))</p>
<table class="data">
    <thead>
    <tr>
        <th>@pdfStr(__('doctors.report_th_date'))</th>
        <th>@pdfStr(__('doctors.report_th_patient'))</th>
        <th>@pdfStr(__('doctors.report_th_invoice'))</th>
        <th>@pdfStr(__('doctors.report_th_total'))</th>
        <th>@pdfStr(__('doctors.report_th_paid'))</th>
        <th>@pdfStr(__('doctors.report_th_doctor_share'))</th>
        <th>@pdfStr(__('doctors.report_th_settlement'))</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
        @php($earning = $inv->doctorEarnings->first())
        <tr>
            <td>{{ $inv->created_at?->format('d/m/Y') }}</td>
            <td>@pdfStr(optional($inv->patient)->full_name ?? __('common.em_dash'))</td>
            <td>{{ $inv->invoice_number }}</td>
            <td>{{ number_format((float) $inv->total, 2) }}</td>
            <td>{{ number_format((float) $inv->paid, 2) }}</td>
            <td>{{ $earning ? number_format((float) $earning->earning_amount, 2) : __('common.em_dash') }}</td>
            <td>
                @if($earning)
                    @pdfStr($earning->status === \App\Models\DoctorEarning::STATUS_PAID ? __('doctors.earning_settlement_paid') : __('doctors.earning_settlement_pending'))
                @else
                    {{ __('common.em_dash') }}
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
