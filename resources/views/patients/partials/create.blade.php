@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $patientDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
<div class="mx-auto w-full max-w-[900px]" dir="{{ $patientDir }}">
    <div class="mb-6">
        <a href="{{ route('patients.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('patients.back_to_patients') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('patients.heading_add_patient') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('patients.subtitle_create') }}</p>
    </div>

    @include('patients.partials.form', [
        'action' => route('patients.store'),
        'method' => 'POST',
    ])
</div>
