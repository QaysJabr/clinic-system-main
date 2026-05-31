@php
    $p = $payload;
@endphp
<x-mail::message>
# {{ __('contact.mail_heading') }}

**{{ __('contact.field_name') }}:** {{ $p['name'] }}

**{{ __('contact.field_email') }}:** {{ $p['email'] }}

@if (! empty($p['phone']))
**{{ __('contact.field_phone') }}:** {{ $p['phone'] }}
@endif

**{{ __('contact.field_subject') }}:** {{ $p['subject'] }}

---

{{ $p['message'] }}

{{ __('contact.mail_footer') }}

{{ config('app.name') }}
</x-mail::message>
