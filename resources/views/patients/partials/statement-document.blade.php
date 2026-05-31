@php
    $clinicTitle = $clinic->clinic_name ?: config('app.name');
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
    $t = $statementTotals;
    $clinicLogoPath = \App\Support\ClinicDocumentLogo::src($clinic, (bool) ($exportRender ?? false));
@endphp

<div class="doc-brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:26%; vertical-align:top; text-align:right;">
                    <img src="{{ $clinicLogoPath }}" alt="" class="doc-logo">
                </td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p class="doc-tag">@pdfStr(__('patients.print_statement_tag'))</p>
                <h1 class="doc-title">@pdfStr($clinicTitle)</h1>
                @if($clinicContact !== '')
                    <p class="doc-sub">{{ $clinicContact }}</p>
                @endif
                <p class="doc-sub" style="margin-top:8px; font-weight:bold; color:#1f2937;">
                    @pdfStr($patient->full_name) — {{ $patient->file_number }}
                </p>
                <p class="doc-sub">
                    @pdfStr(__('patients.period_label', ['range' => $reportMeta['date_range_label'] ?? __('patients.em_dash')]))
                </p>
                <p class="doc-sub">
                    @pdfStr(__('patients.print_date', ['datetime' => $generatedAt->format('d/m/Y H:i')]))
                </p>
                @if(! empty($reportMeta['filter_lines']))
                    @foreach($reportMeta['filter_lines'] as $line)
                        @if(filled($line))
                            <p class="doc-sub">@pdfStr($line)</p>
                        @endif
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
</div>

<table class="doc-stats">
    <tr>
        <td class="doc-stat doc-stat--slate">
            <p class="doc-stat-label">@pdfStr(__('patients.statement_total_invoiced_debit'))</p>
            <p class="doc-stat-value">{{ number_format($t['total_invoiced'], 2) }} {{ $cur }}</p>
        </td>
        <td class="doc-stat doc-stat--teal">
            <p class="doc-stat-label">@pdfStr(__('patients.statement_total_paid_credit'))</p>
            <p class="doc-stat-value">{{ number_format($t['total_paid'], 2) }} {{ $cur }}</p>
        </td>
        <td class="doc-stat doc-stat--rose">
            <p class="doc-stat-label">@pdfStr(__('patients.statement_ending_balance'))</p>
            <p class="doc-stat-value">{{ number_format($t['ending_balance'], 2) }} {{ $cur }}</p>
        </td>
    </tr>
</table>

<p class="doc-section">@pdfStr(__('patients.pdf_movements'))</p>
<table class="doc-table">
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
    @forelse($ledger as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td>@pdfStr($row['type'])</td>
            <td>{{ $row['ref'] }}</td>
            <td>@pdfStr($row['description'])</td>
            <td class="doc-debit">{{ $row['debit'] ?? __('patients.em_dash') }}</td>
            <td class="doc-credit">{{ $row['credit'] ?? __('patients.em_dash') }}</td>
            <td style="font-weight:bold;">{{ $row['balance'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align:center; color:#6b7280;">@pdfStr(__('patients.empty_ledger'))</td>
        </tr>
    @endforelse
    </tbody>
</table>
