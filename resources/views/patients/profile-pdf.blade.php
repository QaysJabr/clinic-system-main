@php
    $clinicLogoPath = ($clinic->clinic_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($clinic->clinic_logo))
        ? str_replace('\\', '/', \Illuminate\Support\Facades\Storage::disk('public')->path($clinic->clinic_logo))
        : null;
    $clinicTitle = $clinic->clinic_name ?: config('app.name', 'Clinic System');
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
    $invStatus = fn ($s) => match ($s) {
        'paid' => __('patients.invoice_status_paid'),
        'partial' => __('patients.invoice_status_partial'),
        default => __('patients.invoice_status_unpaid'),
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>profile</title>
    <style>
        @include('partials.pdf-font-rules')
        * { box-sizing: border-box; }
        body, table, th, td { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111827; margin: 0; padding: 16px; }
        .brand { border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; color: #0F4C81; margin: 0; }
        .sub { font-size: 9px; color: #6b7280; margin: 3px 0 0; }
        .brand-logo { max-height: 40px; max-width: 100px; }
        .section { margin: 10px 0 4px; font-size: 8px; font-weight: bold; color: #9ca3af; text-align: right; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 4px 5px; text-align: right; }
        table.data th { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
<div class="brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:22%; vertical-align:top; text-align:right;"><img src="{{ $clinicLogoPath }}" alt="" class="brand-logo"></td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p class="sub" style="font-weight:bold; color:#9ca3af;">@pdfStr(__('patients.pdf_patient_file'))</p>
                <h1 class="title">@pdfStr($clinicTitle)</h1>
                <p class="sub">@pdfStr($patient->full_name) — {{ $patient->file_number }}</p>
                @if($clinicContact !== '')
                    <p class="sub">{{ $clinicContact }}</p>
                @endif
                <p class="sub">@pdfStr(__('patients.pdf_printed_at_line', ['datetime' => $generatedAt->format('d/m/Y H:i')]))</p>
            </td>
        </tr>
    </table>
</div>
@include('reports.partials.pdf-standard-meta')

<p class="section">@pdfStr(__('patients.pdf_financial_summary'))</p>
<table class="data">
    <tr><th>@pdfStr(__('patients.summary_visit_count'))</th><td>{{ $summary['visit_count'] }}</td><th>@pdfStr(__('patients.summary_invoice_count'))</th><td>{{ $summary['invoice_count'] }}</td></tr>
    <tr><th>@pdfStr(__('patients.summary_invoice_total'))</th><td>{{ number_format($summary['invoice_total'], 2) }} {{ $cur }}</td><th>@pdfStr(__('patients.summary_paid'))</th><td>{{ number_format($summary['paid_total'], 2) }} {{ $cur }}</td></tr>
    <tr><th>@pdfStr(__('patients.summary_balance'))</th><td>{{ number_format($summary['balance'], 2) }} {{ $cur }}</td><th>@pdfStr(__('patients.summary_last_visit'))</th><td>@if($summary['last_visit_date']){{ \Illuminate\Support\Carbon::parse($summary['last_visit_date'])->format('d/m/Y') }}@else @pdfStr(__('patients.em_dash')) @endif</td></tr>
</table>

<p class="section">@pdfStr(__('patients.pdf_patient_data'))</p>
<table class="data">
    <tr><th>@pdfStr(__('patients.field_phone'))</th><td>@pdfStr($patient->phone ?: __('patients.em_dash'))</td><th>@pdfStr(__('patients.field_date_of_birth'))</th><td>{{ $patient->date_of_birth?->format('d/m/Y') ?? __('patients.em_dash') }}</td></tr>
    <tr><th>@pdfStr(__('patients.field_gender'))</th><td>@pdfStr(match ($patient->gender) { 'male', 'm' => __('patients.gender_male'), 'female', 'f' => __('patients.gender_female'), default => $patient->gender ? (string) $patient->gender : __('patients.em_dash') })</td><th>@pdfStr(__('patients.field_registration_date'))</th><td>{{ $patient->created_at?->format('d/m/Y') ?? __('patients.em_dash') }}</td></tr>
    @if(filled($patient->address))
        <tr><th>@pdfStr(__('patients.field_address'))</th><td colspan="3">@pdfStr($patient->address)</td></tr>
    @endif
</table>

@if($canViewClinical && $visits->isNotEmpty())
    <p class="section">@pdfStr(__('patients.pdf_visits'))</p>
    <table class="data">
        <thead><tr><th>@pdfStr(__('patients.col_date'))</th><th>@pdfStr(__('patients.label_doctor'))</th><th>@pdfStr(__('patients.col_chief_complaint'))</th><th>@pdfStr(__('patients.pdf_col_diagnosis'))</th><th>@pdfStr(__('patients.field_status'))</th></tr></thead>
        <tbody>
        @foreach($visits as $v)
            <tr>
                <td>{{ $v->visit_date?->format('d/m/Y') ?? '' }}</td>
                <td>@pdfStr($v->doctor->full_name ?? __('patients.em_dash'))</td>
                <td>@pdfStr(\Illuminate\Support\Str::limit((string) ($v->chief_complaint ?? ''), 50))</td>
                <td>@pdfStr(\Illuminate\Support\Str::limit((string) ($v->diagnosis ?? ''), 50))</td>
                <td>@pdfStr($v->statusLabel())</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($canViewClinical && $appointments->isNotEmpty())
    <p class="section">@pdfStr(__('patients.pdf_appointments'))</p>
    <table class="data">
        <thead><tr><th>@pdfStr(__('patients.col_date'))</th><th>@pdfStr(__('patients.pdf_col_time'))</th><th>@pdfStr(__('patients.label_doctor'))</th><th>@pdfStr(__('patients.field_status'))</th><th>@pdfStr(__('patients.field_notes'))</th></tr></thead>
        <tbody>
        @foreach($appointments as $a)
            <tr>
                <td>{{ $a->appointment_date?->format('d/m/Y') ?? '' }}</td>
                <td>{{ $a->start_time ?? __('patients.em_dash') }}</td>
                <td>@pdfStr($a->doctor->full_name ?? __('patients.em_dash'))</td>
                <td>@pdfStr(match ($a->status) { 'scheduled' => __('patients.appointment_status_scheduled'), 'completed' => __('patients.appointment_status_completed'), 'cancelled' => __('patients.appointment_status_cancelled'), default => $a->status })</td>
                <td>@pdfStr(\Illuminate\Support\Str::limit((string) ($a->notes ?? ''), 40))</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($invoices->isNotEmpty())
    <p class="section">@pdfStr(__('patients.pdf_invoices'))</p>
    <table class="data">
        <thead><tr><th>@pdfStr(__('patients.pdf_col_number'))</th><th>@pdfStr(__('patients.col_date'))</th><th>@pdfStr(__('patients.label_doctor'))</th><th>@pdfStr(__('patients.col_total'))</th><th>@pdfStr(__('patients.summary_paid'))</th><th>@pdfStr(__('patients.col_remaining'))</th><th>@pdfStr(__('patients.field_status'))</th></tr></thead>
        <tbody>
        @foreach($invoices as $inv)
            @php $rem = (float) $inv->total - (float) $inv->paid; @endphp
            <tr>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ $inv->created_at?->format('d/m/Y') ?? '' }}</td>
                <td>@pdfStr($inv->treatingDoctor->full_name ?? __('patients.em_dash'))</td>
                <td>{{ number_format((float) $inv->total, 2) }}</td>
                <td>{{ number_format((float) $inv->paid, 2) }}</td>
                <td>{{ number_format($rem, 2) }}</td>
                <td>@pdfStr($invStatus($inv->status))</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($payments->isNotEmpty())
    <p class="section">@pdfStr(__('patients.pdf_payments'))</p>
    <table class="data">
        <thead><tr><th>@pdfStr(__('patients.col_payment_date'))</th><th>@pdfStr(__('patients.pdf_col_invoice'))</th><th>@pdfStr(__('patients.col_amount'))</th><th>@pdfStr(__('patients.col_payment_method'))</th><th>@pdfStr(__('patients.field_notes'))</th></tr></thead>
        <tbody>
        @foreach($payments as $pay)
            <tr>
                <td>{{ $pay->payment_date?->format('d/m/Y') ?? '' }}</td>
                <td>{{ $pay->invoice->invoice_number ?? __('patients.em_dash') }}</td>
                <td>{{ number_format((float) $pay->amount, 2) }}</td>
                <td>@pdfStr($pay->payment_method ?: __('patients.em_dash'))</td>
                <td>@pdfStr(\Illuminate\Support\Str::limit((string) ($pay->notes ?? ''), 35))</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@include('reports.partials.pdf-patient-statement-totals')

<p class="section">@pdfStr(__('patients.pdf_profile_statement_movements'))</p>
<table class="data">
    <thead>
    <tr>
        <th>@pdfStr(__('patients.col_date'))</th>
        <th>@pdfStr(__('patients.ledger_col_type'))</th>
        <th>@pdfStr(__('patients.ledger_col_ref'))</th>
        <th>@pdfStr(__('patients.ledger_col_description'))</th>
        <th>@pdfStr(__('patients.ledger_col_debit'))</th>
        <th>@pdfStr(__('patients.ledger_col_credit'))</th>
        <th>@pdfStr(__('patients.ledger_col_balance'))</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ledger as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td>@pdfStr($row['type'])</td>
            <td>{{ $row['ref'] }}</td>
            <td>@pdfStr($row['description'])</td>
            <td>{{ $row['debit'] ?? __('patients.em_dash') }}</td>
            <td>{{ $row['credit'] ?? __('patients.em_dash') }}</td>
            <td>{{ $row['balance'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
