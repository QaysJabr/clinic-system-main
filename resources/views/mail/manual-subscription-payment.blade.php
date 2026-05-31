<x-mail::message>
# {{ __('platform.mail_payment_recorded_heading') }}

{{ __('platform.mail_payment_recorded_intro', ['clinic' => $clinicName, 'amount' => $amount]) }}

**{{ __('platform.col_paid_at') }}:** {{ $paidAt }}

@if ($expiresAt)
**{{ __('platform.col_expires') }}:** {{ $expiresAt }}
@endif

@if ($notes)
**{{ __('platform.notes') }}:** {{ $notes }}
@endif

<x-mail::button :url="$billingUrl">
{{ __('platform.mail_payment_recorded_btn_billing') }}
</x-mail::button>

{{ __('platform.mail_payment_recorded_footer') }}

{{ config('app.name') }}
</x-mail::message>
