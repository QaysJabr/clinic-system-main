@php
    $statusLabel = match ($invoice->status) {
        'paid' => __('common.paid'),
        'partial' => __('common.partial'),
        default => __('common.unpaid'),
    };
    $remaining = (float) $invoice->total - (float) $invoice->paid;
    $clinicLogoPath = \App\Support\ClinicDocumentLogo::src($clinic, (bool) ($exportRender ?? false));
    $clinicTitle = $clinic->clinic_name ?: config('app.name', __('invoices.title'));
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
    $cur = $clinic->currency;
@endphp

<div class="doc-brand">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if($clinicLogoPath)
                <td style="width:28%; vertical-align:top; text-align:right;">
                    <img src="{{ $clinicLogoPath }}" alt="" class="doc-logo">
                </td>
            @endif
            <td style="vertical-align:top; text-align:right;">
                <p class="doc-tag">{{ __('invoices.print_document_tag') }}</p>
                <h1 class="doc-title">{{ $clinicTitle }}</h1>
                @if($clinicContact !== '')
                    <p class="doc-sub">{{ $clinicContact }}</p>
                @endif
                @if(filled($clinic->clinic_address))
                    <p class="doc-sub" style="white-space:pre-wrap;">{{ $clinic->clinic_address }}</p>
                @elseif($clinicContact === '')
                    <p class="doc-sub">{{ __('invoices.print_clinic_fallback') }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

<table style="width:100%; border-collapse:separate; border-spacing:12px 0; margin-bottom:16px;">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <div class="doc-panel">
                <h2 class="doc-panel-title">{{ __('invoices.section_invoice_details') }}</h2>
                <div class="doc-kv"><span>{{ __('invoices.field_invoice_number') }}</span><strong>{{ $invoice->invoice_number }}</strong></div>
                <div class="doc-kv"><span>{{ __('invoices.field_created_at') }}</span><strong>@safeDate($invoice->created_at)</strong></div>
                <div class="doc-kv"><span>{{ __('invoices.field_status') }}</span><strong>{{ $statusLabel }}</strong></div>
            </div>
        </td>
        <td style="width:50%; vertical-align:top;">
            <div class="doc-panel">
                <h2 class="doc-panel-title">{{ __('invoices.section_patient_details') }}</h2>
                <div class="doc-kv"><span>{{ __('invoices.field_patient') }}</span><strong>{{ optional($invoice->patient)->full_name ?? __('common.em_dash') }}</strong></div>
                @if(optional($invoice->patient)->phone)
                    <div class="doc-kv"><span>{{ __('validation.attributes.phone') }}</span><strong>{{ $invoice->patient->phone }}</strong></div>
                @endif
                @if($invoice->visit)
                    <div class="doc-kv"><span>{{ __('invoices.field_visit_line') }}</span><strong>{{ $invoice->visit->chief_complaint ?: __('common.em_dash') }}</strong></div>
                @endif
            </div>
        </td>
    </tr>
</table>

<p class="doc-section">{{ __('invoices.section_line_items') }}</p>
<table class="doc-table">
    <thead>
    <tr>
        <th>{{ __('invoices.th_item_name') }}</th>
        <th>{{ __('invoices.label_price') }}</th>
        <th>{{ __('invoices.label_quantity') }}</th>
        <th>{{ __('invoices.th_line_total') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($invoice->items as $item)
        <tr>
            <td>{{ $item->item_name }}</td>
            <td>{{ number_format((float) $item->price, 2) }}@if(filled($cur)) <span class="doc-cur">{{ $cur }}</span>@endif</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format((float) $item->total, 2) }}@if(filled($cur)) <span class="doc-cur">{{ $cur }}</span>@endif</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="text-align:center;color:#6b7280;">{{ __('invoices.no_line_items') }}</td>
        </tr>
    @endforelse
    </tbody>
</table>

@if($invoice->payments->isNotEmpty())
    <p class="doc-section">{{ __('invoices.section_payments') }}</p>
    <table class="doc-table">
        <thead>
        <tr>
            <th>{{ __('common.amount') }}</th>
            <th>{{ __('invoices.payment_field_method') }}</th>
            <th>{{ __('invoices.payment_field_date') }}</th>
            <th>{{ __('invoices.field_notes') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->payments as $payment)
            <tr>
                <td>{{ number_format((float) $payment->amount, 2) }}@if(filled($cur)) <span class="doc-cur">{{ $cur }}</span>@endif</td>
                <td>{{ \App\Support\PaymentMethods::label((string) $payment->payment_method) }}</td>
                <td>@safeDate($payment->payment_date)</td>
                <td>{{ $payment->notes ?? __('common.em_dash') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<div class="doc-totals">
    <div class="doc-totals-row">
        <span>{{ __('invoices.field_total_amount') }}</span>
        <strong class="doc-totals-big">{{ number_format((float) $invoice->total, 2) }}@if(filled($cur)) <span class="doc-cur">{{ $cur }}</span>@endif</strong>
    </div>
    <div class="doc-totals-row">
        <span>{{ __('invoices.field_paid') }}</span>
        <strong class="doc-credit">{{ number_format((float) $invoice->paid, 2) }}@if(filled($cur)) <span class="doc-cur">{{ $cur }}</span>@endif</strong>
    </div>
    <div class="doc-totals-row doc-totals-row--final">
        <span>{{ __('invoices.field_remaining') }}</span>
        <strong style="color:#b45309;">{{ number_format($remaining, 2) }}@if(filled($cur)) <span class="doc-cur">{{ $cur }}</span>@endif</strong>
    </div>
</div>

@if(filled($clinic->invoice_notes))
    <div class="doc-notes doc-notes--dashed">
        <h2>{{ __('invoices.section_general_notes') }}</h2>
        <p>{{ $clinic->invoice_notes }}</p>
    </div>
@endif

@if($invoice->notes)
    <div class="doc-notes">
        <h2>{{ __('invoices.field_notes') }}</h2>
        <p>{{ $invoice->notes }}</p>
    </div>
@endif
