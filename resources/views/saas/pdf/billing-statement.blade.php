@php
    use App\Services\Saas\ClinicBillingPageService;
    $statusLabel = ($alertTier ?? '') === 'expired'
        ? __('saas.status_expired')
        : (($isActive ?? false) ? __('saas.status_active') : __('saas.status_inactive'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('saas.pdf_statement_title') }}</title>
    <style>
        @include('partials.pdf-font-rules')
        body, table, th, td, p, h1, h2 { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 12px; color: #111827; margin: 0; padding: 24px; }
        h1 { font-size: 20px; color: #0F4C81; margin: 0 0 4px; }
        .meta { font-size: 11px; color: #6b7280; margin-bottom: 20px; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; }
        .row { display: table; width: 100%; font-size: 11px; padding: 3px 0; }
        .row dt { display: table-cell; color: #6b7280; width: 42%; }
        .row dd { display: table-cell; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; }
        th { background: #f9fafb; font-weight: bold; }
        .amount { text-align: left; direction: ltr; }
    </style>
</head>
<body>
    <h1>{{ __('saas.pdf_statement_title') }}</h1>
    <p class="meta">{{ __('saas.pdf_printed_at', ['datetime' => now()->format('d/m/Y H:i')]) }}</p>

    <div class="box">
        <div class="row"><dt>{{ __('saas.label_clinic') }}</dt><dd>{{ $clinic->name }}</dd></div>
        <div class="row"><dt>{{ __('saas.label_plan') }}</dt><dd>{{ \App\Support\PlanDisplay::localizedName($plan, $clinic->subscription_plan ?: null) }}</dd></div>
        <div class="row"><dt>{{ __('saas.label_subscription_status') }}</dt><dd>{{ $statusLabel }}</dd></div>
        <div class="row"><dt>{{ __('saas.label_billing_cycle') }}</dt><dd>{{ $billingCycleLabel ?? '—' }}</dd></div>
        <div class="row"><dt>{{ __('saas.label_amount') }}</dt><dd>{{ $subscriptionAmount ?? '—' }}</dd></div>
        <div class="row"><dt>{{ __('saas.label_expires') }}</dt><dd>{{ $clinic->subscription_expires_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
        <div class="row"><dt>{{ __('saas.label_days_remaining') }}</dt><dd>{{ $daysRemaining !== null ? $daysRemaining : '—' }}</dd></div>
    </div>

    @if ($usage)
        <div class="box">
            <strong>{{ __('saas.section_usage') }}</strong>
            <div class="row"><dt>{{ __('saas.usage_patients') }}</dt><dd>{{ $usage['patients'] }}@if(! $usage['patients_unlimited']) / {{ $usage['max_patients'] }}@endif</dd></div>
            <div class="row"><dt>{{ __('saas.usage_users') }}</dt><dd>{{ $usage['users'] }}@if(! $usage['users_unlimited']) / {{ $usage['max_users'] }}@endif</dd></div>
        </div>
    @endif

    <h2 style="font-size: 14px; margin: 0 0 8px;">{{ __('saas.section_payments') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('common.date') }}</th>
                <th class="amount">{{ __('saas.th_amount') }}</th>
                <th>{{ __('saas.th_source') }}</th>
                <th>{{ __('common.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="amount">{{ number_format((float) $payment->amount, 2) }}</td>
                    <td>{{ ClinicBillingPageService::paymentSourceLabel($payment->source) }}</td>
                    <td>{{ ClinicBillingPageService::paymentStatusLabel($payment->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('saas.empty_payments') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
