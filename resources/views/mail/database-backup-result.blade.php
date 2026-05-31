<x-mail::message>
@if ($success)
# {{ __('backups.mail_success_heading') }}
@else
# {{ __('backups.mail_failure_heading') }}
@endif

{{ $bodyMessage }}

@if ($filename)
**{{ __('backups.col_filename') }}:** `{{ $filename }}`
@endif

<x-mail::button :url="$appUrl">
{{ __('backups.mail_open_app') }}
</x-mail::button>

@if ($whatsappUrl)
<x-mail::button :url="$whatsappUrl" color="success">
{{ __('backups.mail_whatsapp_cta') }}
</x-mail::button>
@endif

{{ __('backups.mail_footer') }}

{{ config('app.name') }}
</x-mail::message>
