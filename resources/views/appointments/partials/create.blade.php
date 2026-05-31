@php
    $apptDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[900px]" dir="{{ $apptDir }}">
    <div class="mb-6">
        <a href="{{ route('appointments.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('appointments.back_to_list') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('appointments.heading_create') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('appointments.subtitle_create') }}</p>
    </div>

    @include('appointments.partials.form', [
        'patients' => $patients,
        'doctors' => $doctors,
        'action' => route('appointments.store'),
        'method' => 'POST',
    ])
</div>
