<x-mail::message>
# {{ __('saas.mail_expiring_heading', ['days' => $daysRemaining]) }}

{{ __('saas.mail_expiring_intro', ['clinic' => $clinicName, 'days' => $daysRemaining]) }}

@if ($expiresAt)
**{{ __('saas.label_expires') }}:** {{ $expiresAt }}
@endif

<x-mail::button :url="$billingUrl">
{{ __('saas.mail_expiring_btn_billing') }}
</x-mail::button>

<x-mail::button :url="$pricingUrl" color="success">
{{ __('saas.btn_renew_now') }}
</x-mail::button>

{{ __('saas.mail_expiring_footer') }}

{{ config('app.name') }}
</x-mail::message>
