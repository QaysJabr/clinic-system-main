@props(['alerts' => [], 'personal' => false, 'currency' => ''])
@if(!empty($alerts))
    <div {{ $attributes->merge(['class' => 'dash-alerts mb-6 flex flex-col gap-3']) }}>
        @if(isset($alerts['open_invoice_count']) && $alerts['open_invoice_count'] > 0)
            <div class="dash-alert dash-alert--amber">
                <span class="font-semibold">{{ __('invoices.dashboard_alert_open_title') }}</span>
                {{ number_format($alerts['open_invoice_count']) }}
                {{ trans_choice('invoices.dashboard_invoice_word', (int) $alerts['open_invoice_count']) }} —
                <a href="{{ route('invoices.index') }}" class="dash-alert-link">{{ __('invoices.dashboard_alert_open_link') }}</a>
            </div>
        @endif
        @if(isset($alerts['pending_doctor_rows']) && $alerts['pending_doctor_rows'] > 0)
            <div class="dash-alert dash-alert--cyan">
                <span class="font-semibold">{{ $personal ? __('dashboard.alert_pending_share_personal') : __('dashboard.alert_pending_share_admin') }}</span>
                {{ number_format($alerts['pending_doctor_amount'] ?? 0, 2) }}@if(filled($currency)) {{ $currency }}@endif
                ({{ number_format($alerts['pending_doctor_rows']) }} {{ __('dashboard.alert_pending_records_suffix') }}) —
                <a href="{{ route('doctor-earnings.index', ['status' => \App\Models\DoctorEarning::STATUS_PENDING]) }}" class="dash-alert-link">{{ __('dashboard.alert_pending_doctor_link') }}</a>
            </div>
        @endif
    </div>
@endif
