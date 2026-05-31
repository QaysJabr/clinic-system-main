@php
    /** @var \App\Models\Visit $visit */
    $visitDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $procRows = old('procedure_rows');
    if (! is_array($procRows)) {
        $procRows = $visit->structuredProcedures->map(fn ($p) => ['name' => $p->name, 'notes' => $p->notes])->values()->all();
    }
    if (count($procRows) === 0) {
        $procRows = [['name' => '', 'notes' => '']];
    }
    $rxRows = old('rx_rows');
    if (! is_array($rxRows)) {
        $rxRows = $visit->structuredPrescriptions->map(fn ($r) => [
            'medication_name' => $r->medication_name,
            'dosage' => $r->dosage,
            'frequency' => $r->frequency,
            'duration' => $r->duration,
            'notes' => $r->notes,
        ])->values()->all();
    }
    if (count($rxRows) === 0) {
        $rxRows = [['medication_name' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'notes' => '']];
    }
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[900px]" dir="{{ $visitDir }}">
    <div class="mb-6">
        <a href="{{ route('visits.show', $visit) }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('visits.back_to_visit') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('visits.heading_edit') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('visits.subtitle_edit') }}</p>
    </div>

    <x-visit.nav :visit="$visit" active="edit" />

    @include('visits.partials.form', [
        'visit' => $visit,
        'patients' => $patients,
        'doctors' => $doctors,
        'appointments' => $appointments,
        'soap' => $soap ?? [],
        'procRows' => $procRows,
        'rxRows' => $rxRows,
        'action' => route('visits.update', $visit),
        'method' => 'PUT',
        'isEdit' => true,
    ])
</div>
