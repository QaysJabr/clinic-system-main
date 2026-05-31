@php
    $visitDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[900px]" dir="{{ $visitDir }}">
    <div class="mb-6">
        <a href="{{ route('visits.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('visits.back_to_list') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('visits.heading_create') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('visits.subtitle_create') }}</p>
    </div>

    <x-visit.nav active="list" />

    @include('visits.partials.form', [
        'patients' => $patients,
        'doctors' => $doctors,
        'appointments' => $appointments,
        'soap' => $soap ?? [],
        'procRows' => is_array(old('procedure_rows')) ? old('procedure_rows') : [['name' => '', 'notes' => '']],
        'rxRows' => is_array(old('rx_rows')) ? old('rx_rows') : [['medication_name' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'notes' => '']],
        'action' => route('visits.store'),
        'method' => 'POST',
        'isEdit' => false,
    ])
</div>
