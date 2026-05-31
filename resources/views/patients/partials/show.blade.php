@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $patientDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
<div class="max-w-[1400px] mx-auto" dir="{{ $patientDir }}">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <div>
                <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('patients.heading_patient_file') }}</h1>
                <p class="text-gray-600 dark:text-[#9CA3AF] text-sm mt-2 m-0">{{ $patient->full_name }} — {{ $patient->file_number }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @include('patients.partials.portal-link-actions', ['patient' => $patient])
                <a href="{{ route('patients.profile', $patient) }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-teal-600 bg-white px-4 py-2.5 text-sm font-semibold text-teal-800 shadow-sm transition hover:bg-teal-50 dark:border-teal-500/50 dark:bg-[#1F2937] dark:text-teal-300 dark:hover:bg-teal-950/30">{{ __('patients.financial_dashboard') }}</a>
                <a href="{{ route('patients.statement', $patient) }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F4C81] shadow-sm transition hover:bg-blue-50 dark:border-[#3B82F6]/50 dark:bg-[#1F2937] dark:text-[#93C5FD] dark:hover:bg-[#1E3A5F]/40">{{ __('patients.account_statement') }}</a>
                <a href="{{ route('patients.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('patients.patient_list') }}</a>
                @can('manage patients')
                    <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('patients.edit_data') }}</a>
                @endcan
            </div>
        </div>

        @include('patients.partials.portal-link-flash')

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90 px-4 py-4 sm:px-5">
                <h2 class="text-base font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('patients.basic_data') }}</h2>
            </div>
            <div class="p-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('patients.col_file_number') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 m-0">{{ $patient->file_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('patients.field_name') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 m-0">{{ $patient->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('patients.field_phone') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0">{{ $patient->phone ?? __('patients.em_dash') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('patients.field_date_of_birth') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0">@safeDate($patient->date_of_birth)</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('patients.field_gender') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0">{{ $patient->gender === 'male' ? __('patients.gender_male') : ($patient->gender === 'female' ? __('patients.gender_female') : __('patients.em_dash')) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('patients.field_status') }}</dt>
                        <dd class="mt-1 m-0">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $patient->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $patient->status === 'active' ? __('patients.status_active') : __('patients.status_inactive') }}</span>
                        </dd>
                    </div>
                </dl>
                @if($patient->notes)
                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <p class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide m-0 mb-1">{{ __('patients.field_notes') }}</p>
                        <p class="text-sm text-gray-700 dark:text-[#E5E7EB] m-0 whitespace-pre-wrap">{{ $patient->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @include('partials.attachments-card', [
            'attachments' => $attachments,
            'uploadUrl' => route('patients.attachments.store', $patient),
        ])
    </div>
