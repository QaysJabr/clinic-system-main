@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $patientDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
<div class="mx-auto w-full max-w-[900px]" dir="{{ $patientDir }}">
    <div class="mb-6">
        <a href="{{ route('patients.show', $patient) }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('patients.medical_record') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('patients.heading_edit_patient') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ $patient->full_name }} — {{ $patient->file_number }}</p>
    </div>

    <x-patient.nav :patient="$patient" active="edit" />

    @include('patients.partials.form', [
        'patient' => $patient,
        'action' => route('patients.update', $patient),
        'method' => 'PUT',
    ])
</div>
