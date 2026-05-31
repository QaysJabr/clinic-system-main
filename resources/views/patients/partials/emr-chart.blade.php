@php
    use App\Enums\ClinicalRecordType;
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@if (! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="w-full min-w-0" dir="{{ $dir }}">
    @include('patients.partials.portal-link-flash')

    {{-- Header --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-[#0F4C81] to-[#1F7A8C] p-6 text-white shadow-lg dark:border-[#374151]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-white/70 m-0">{{ __('patients.col_file_number') }}</p>
                <h1 class="text-2xl font-bold m-0 mt-1">{{ $patient->full_name }}</h1>
                <p class="text-sm text-white/80 m-0 mt-2">{{ $patient->file_number }} · @safeDate($patient->date_of_birth) · {{ $patient->phone ?? __('common.em_dash') }}</p>
                @if (! empty($riskFlags))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($riskFlags as $flag)
                            <span class="inline-flex rounded-full bg-red-500/90 px-2.5 py-0.5 text-xs font-bold">{{ $flag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <a href="{{ route('patients.profile', $patient) }}" data-spa class="inline-flex items-center rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold hover:bg-white/25">{{ __('emr.view_financial') }}</a>
                @can('viewPatientClinical', $patient)
                    <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}" data-spa class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#0F4C81]">{{ __('emr.new_visit') }}</a>
                @endcan
                @can('view', $patient)
                    <form method="POST" action="{{ route('patients.portal-link', $patient) }}" class="inline" data-no-spa data-portal-link-form>
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-lg border border-white/40 px-4 py-2 text-sm font-semibold hover:bg-white/10">{{ __('portal.share_portal') }}</button>
                    </form>
                @endcan
                <a href="{{ route('patients.qr-card', $patient) }}" target="_blank" class="inline-flex items-center rounded-lg border border-white/40 px-4 py-2 text-sm font-semibold">{{ __('emr.qr_card') }}</a>
                @can('manage patients')
                    <a href="{{ route('patients.edit', $patient) }}" data-spa class="inline-flex items-center rounded-lg border border-white/40 px-4 py-2 text-sm font-semibold">{{ __('patients.edit_data') }}</a>
                @endcan
            </div>
        </div>
    </div>

    <x-patient.nav :patient="$patient" active="emr" />

    @if ($canViewClinical)
        <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="text-xs font-bold uppercase text-gray-400 m-0">{{ __('emr.clinical_allergy') }}</p>
                <p class="text-2xl font-bold text-[#0F4C81] m-0 mt-1">{{ ($clinicalGrouped['allergy'] ?? collect())->count() }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="text-xs font-bold uppercase text-gray-400 m-0">{{ __('emr.clinical_chronic_condition') }}</p>
                <p class="text-2xl font-bold text-[#0F4C81] m-0 mt-1">{{ ($clinicalGrouped['chronic_condition'] ?? collect())->count() }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="text-xs font-bold uppercase text-gray-400 m-0">{{ __('emr.clinical_medication') }}</p>
                <p class="text-2xl font-bold text-[#0F4C81] m-0 mt-1">{{ ($clinicalGrouped['medication'] ?? collect())->count() }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="text-xs font-bold uppercase text-gray-400 m-0">{{ __('emr.heading_diagnosis_history') }}</p>
                <p class="text-2xl font-bold text-[#0F4C81] m-0 mt-1">{{ $diagnosisHistory?->total() ?? 0 }}</p>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-start">
            <div class="min-w-0 w-full space-y-6">
                {{-- Clinical profile --}}
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-[#374151]">
                        <h2 class="text-base font-bold m-0">{{ __('emr.heading_clinical_profile') }}</h2>
                    </div>
                    <div class="p-5 space-y-5">
                        @foreach (ClinicalRecordType::cases() as $type)
                            @php $items = $clinicalGrouped[$type->value] ?? collect(); @endphp
                            <div>
                                <h3 class="text-xs font-bold uppercase text-gray-400 m-0 mb-2">{{ __($type->labelKey()) }}</h3>
                                @forelse ($items as $item)
                                    <div class="mb-2 flex items-start justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-[#111827]">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold m-0">{{ $item->title }}</p>
                                            @if ($item->details)
                                                <p class="text-xs text-gray-500 m-0 mt-0.5">{{ $item->details }}</p>
                                            @endif
                                        </div>
                                        @if ($canManageClinical)
                                            <form method="POST" action="{{ route('patients.clinical.destroy', [$patient, $item]) }}" class="shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">{{ __('emr.remove_record') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 m-0">{{ __('common.em_dash') }}</p>
                                @endforelse
                            </div>
                        @endforeach

                        @if ($canManageClinical)
                            <form method="POST" action="{{ route('patients.clinical.store', $patient) }}" class="mt-4 space-y-3 rounded-lg border border-dashed border-gray-200 p-4 dark:border-[#4B5563]">
                                @csrf
                                <p class="text-sm font-semibold m-0">{{ __('emr.add_clinical_record') }}</p>
                                <select name="type" required class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827]">
                                    @foreach (ClinicalRecordType::cases() as $type)
                                        <option value="{{ $type->value }}">{{ __($type->labelKey()) }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="title" required placeholder="{{ __('patients.field_name') }}" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827]">
                                <textarea name="details" rows="2" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827]"></textarea>
                                <button type="submit" class="rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-semibold text-white">{{ __('common.save') }}</button>
                            </form>
                        @endif
                    </div>
                </section>

                @if ($diagnosisHistory)
                    <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-[#374151]">
                            <h2 class="text-base font-bold m-0">{{ __('emr.heading_diagnosis_history') }}</h2>
                        </div>
                        <div class="p-5">
                            <form method="GET" action="{{ route('patients.show', $patient) }}" class="mb-4">
                                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('emr.search_diagnoses') }}" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            </form>
                            <ul class="space-y-2">
                                @forelse ($diagnosisHistory as $dx)
                                    <li class="rounded-lg border border-gray-100 px-3 py-2 text-sm dark:border-[#374151]">
                                        <span class="font-semibold">{{ $dx->description }}</span>
                                        @if ($dx->icd_code)<span class="text-gray-500">({{ $dx->icd_code }})</span>@endif
                                        <span class="block text-xs text-gray-400 mt-0.5">@safeDate($dx->diagnosed_at) · {{ optional($dx->doctor)->full_name }}</span>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500">{{ __('common.no_results') }}</li>
                                @endforelse
                            </ul>
                            <div class="mt-3 overflow-x-auto">{{ $diagnosisHistory->links() }}</div>
                        </div>
                    </section>
                @endif
            </div>

            <div class="min-w-0 w-full">
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-[#374151]">
                        <h2 class="text-base font-bold m-0">{{ __('emr.heading_timeline') }}</h2>
                    </div>
                    <div class="p-5">
                        <ol class="m-0 list-none space-y-5 border-s-2 border-gray-200 ps-5 dark:border-[#4B5563]">
                            @forelse ($timeline as $item)
                                <li class="flex gap-3">
                                    <span class="mt-1.5 flex h-3 w-3 shrink-0 rounded-full bg-[#0F4C81] ring-4 ring-white dark:ring-[#1F2937]" aria-hidden="true"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-gray-900 dark:text-[#F3F4F6] m-0">{{ $item['title'] }}</p>
                                                <p class="text-xs text-gray-500 m-0 mt-0.5">{{ $item['occurred_at']->format('d/m/Y H:i') }}</p>
                                                <p class="text-sm text-gray-600 dark:text-[#9CA3AF] m-0 mt-1 break-words">{{ $item['summary'] }}</p>
                                            </div>
                                            @if (! empty($item['badge']))
                                                <span class="inline-flex shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold {{ $item['badge_class'] ?? 'bg-gray-100 text-gray-700' }}">{{ $item['badge'] }}</span>
                                            @endif
                                        </div>
                                        @if (! empty($item['url']))
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <a href="{{ $item['url'] }}" @if (str_starts_with($item['url'], url('/attachments'))) target="_blank" @else data-spa @endif class="text-xs font-semibold text-[#0F4C81] hover:underline">{{ __('common.edit') }}</a>
                                                @if (! empty($item['preview_url']))
                                                    <a href="{{ $item['preview_url'] }}" target="_blank" class="text-xs font-semibold text-teal-700 hover:underline">{{ __('emr.preview') }}</a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">{{ __('common.no_results') }}</li>
                            @endforelse
                        </ol>
                    </div>
                </section>
            </div>
        </div>
    @else
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ __('patients.clinical_access_denied') }}</p>
    @endif

    <div class="min-w-0">
        @include('partials.attachments-card', [
            'attachments' => $attachments,
            'uploadUrl' => route('patients.attachments.store', $patient),
            'showCategories' => true,
        ])
    </div>
</div>
