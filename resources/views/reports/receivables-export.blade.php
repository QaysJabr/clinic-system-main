@extends('layouts.export')

@section('title', __('invoices.receivables_meta_title'))

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
                    <p class="doc-tag">{{ __('invoices.receivables_nav_reports') }}</p>
                    <h1 class="doc-title">{{ $clinicTitle }}</h1>
                    <p class="doc-sub">{{ __('invoices.receivables_meta_title') }}</p>
                    @if($clinicContact !== '')
                        <p class="doc-sub">{{ $clinicContact }}</p>
                    @endif
                    <p class="doc-sub">{{ __('invoices.receivables_pdf_print_date', ['datetime' => $generatedAt->format('d/m/Y H:i')]) }}</p>
                </td>
            </tr>
        </table>
    </div>

    @include('reports.partials.pdf-standard-meta')
    @include('reports.partials.pdf-unified-summary')

    <p class="doc-section">{{ __('invoices.receivables_pdf_total_remaining_filtered') }}</p>
    <p style="font-size:14px;font-weight:bold;color:#047857;">{{ number_format((float) $totalReceivables, 2) }} {{ $cur }}</p>

    <p class="doc-section">{{ __('invoices.receivables_pdf_section_table') }}</p>
    <table class="doc-table">
        <thead>
        <tr>
            <th>{{ __('invoices.field_patient') }}</th>
            <th>{{ __('invoices.field_invoice_number') }}</th>
            <th>{{ __('invoices.field_doctor') }}</th>
            <th>{{ __('invoices.field_total') }}</th>
            <th>{{ __('invoices.field_paid') }}</th>
            <th>{{ __('invoices.field_remaining') }}</th>
            <th>{{ __('invoices.field_status') }}</th>
            <th>{{ __('invoices.field_due_date_column') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoices as $inv)
            @php($rem = round((float) $inv->total - (float) $inv->paid, 2))
            <tr>
                <td>{{ optional($inv->patient)->full_name ?? __('common.em_dash') }}</td>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ optional($inv->treatingDoctor)->full_name ?? __('common.em_dash') }}</td>
                <td>{{ number_format((float) $inv->total, 2) }}</td>
                <td>{{ number_format((float) $inv->paid, 2) }}</td>
                <td>{{ number_format($rem, 2) }}</td>
                <td>{{ $inv->status === 'partial' ? __('common.partial') : __('common.unpaid') }}</td>
                <td>{{ $inv->due_date?->format('d/m/Y') ?? __('common.em_dash') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
