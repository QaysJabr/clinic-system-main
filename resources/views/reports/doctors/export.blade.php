@extends('layouts.export')

@section('title', __('doctors.report_heading'))

@push('head')
    <style>
        @include('partials.pdf-document-styles')
    </style>
@endpush

@section('content')
    @php
        $clinicTitle = $clinic->clinic_name ?: config('app.name', 'Clinic System');
        $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
        $clinicLogoSrc = \App\Support\ClinicDocumentLogo::src($clinic, true);
    @endphp

    <div class="doc-brand">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                @if($clinicLogoSrc)
                    <td style="width:24%; vertical-align:top; text-align:right;"><img src="{{ $clinicLogoSrc }}" alt="" class="doc-logo"></td>
                @endif
                <td style="vertical-align:top; text-align:right;">
                    <p class="doc-tag">{{ __('doctors.pdf_tag') }}</p>
                    <h1 class="doc-title">{{ $clinicTitle }}</h1>
                    <p class="doc-sub">{{ __('doctors.pdf_doctor_report_label') }}: {{ $doctor->full_name }}</p>
                    @if($clinicContact !== '')
                        <p class="doc-sub">{{ $clinicContact }}</p>
                    @endif
                    <p class="doc-sub">{{ __('patients.pdf_printed_at_line', ['datetime' => $generatedAt->format('d/m/Y H:i')]) }}</p>
                </td>
            </tr>
        </table>
    </div>

    @include('reports.partials.pdf-standard-meta')
    @include('reports.partials.pdf-unified-summary')

    <p class="doc-section">{{ __('doctors.pdf_section_doctor_summary') }}</p>
    <table class="doc-table">
        <tbody>
        <tr><th>{{ __('doctors.pdf_stat_invoice_count') }}</th><td>{{ $stats['invoice_count'] }}</td></tr>
        <tr><th>{{ __('doctors.pdf_stat_accrual') }}</th><td>{{ number_format($stats['accrual_revenue'], 2) }} {{ $cur }}</td></tr>
        <tr><th>{{ __('doctors.pdf_stat_doctor_share') }}</th><td>{{ number_format($stats['doctor_share_total'], 2) }} {{ $cur }}</td></tr>
        <tr><th>{{ __('doctors.pdf_stat_pending') }}</th><td>{{ number_format($stats['earnings_pending'], 2) }} {{ $cur }}</td></tr>
        <tr><th>{{ __('doctors.pdf_stat_paid') }}</th><td>{{ number_format($stats['earnings_paid'], 2) }} {{ $cur }}</td></tr>
        </tbody>
    </table>

    <p class="doc-section">{{ __('doctors.pdf_section_invoices') }}</p>
    <table class="doc-table">
        <thead>
        <tr>
            <th>{{ __('doctors.report_th_date') }}</th>
            <th>{{ __('doctors.report_th_patient') }}</th>
            <th>{{ __('doctors.report_th_invoice') }}</th>
            <th>{{ __('doctors.report_th_total') }}</th>
            <th>{{ __('doctors.report_th_paid') }}</th>
            <th>{{ __('doctors.report_th_doctor_share') }}</th>
            <th>{{ __('doctors.report_th_settlement') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoices as $inv)
            @php($earning = $inv->doctorEarnings->first())
            <tr>
                <td>{{ $inv->created_at?->format('d/m/Y') }}</td>
                <td>{{ optional($inv->patient)->full_name ?? __('common.em_dash') }}</td>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ number_format((float) $inv->total, 2) }}</td>
                <td>{{ number_format((float) $inv->paid, 2) }}</td>
                <td>{{ $earning ? number_format((float) $earning->earning_amount, 2) : __('common.em_dash') }}</td>
                <td>
                    @if($earning)
                        {{ $earning->status === \App\Models\DoctorEarning::STATUS_PAID ? __('doctors.earning_settlement_paid') : __('doctors.earning_settlement_pending') }}
                    @else
                        {{ __('common.em_dash') }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
