@php
    $appointment = $reminder->appointment;
    $patient = $appointment?->patient;
    $doctor = $appointment?->doctor;
    $dateStr = $appointment?->appointment_date?->format('Y-m-d') ?? '';
    $start = $appointment?->start_time ?? '';
    $lead = (int) ($reminder->payload['lead_hours'] ?? 0);
@endphp
<x-mail::message>
# {{ __('mail.appointment_reminder_heading') }}

{{ __('mail.appointment_reminder_intro', ['hours' => $lead]) }}

**{{ __('appointments.nav_appointments') }}:** {{ $dateStr }} {{ $start }}

@if ($patient)
**{{ __('patients.title') }}:** {{ $patient->full_name }}
@endif

@if ($doctor)
**{{ __('doctors.title') }}:** {{ $doctor->full_name }}
@endif

@if ($appointment?->reason)
**{{ __('appointments.field_reason') }}:** {{ $appointment->reason }}
@endif

<x-mail::button :url="url('/appointments')">
{{ __('mail.appointment_reminder_btn') }}
</x-mail::button>

{{ __('mail.appointment_reminder_footer') }}

{{ config('app.name') }}
</x-mail::message>
