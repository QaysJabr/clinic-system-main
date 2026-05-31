@php
    $visitDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $visitDateStr = $visit->visit_date ? $visit->visit_date->format('d/m/Y') : __('common.em_dash');
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $visitDir }}">
        <div class="mb-6">
            <a href="{{ route('visits.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('visits.back_to_list') }}</a>
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    @if ($visit->patient)
                        <x-patient.avatar :name="$visit->patient->full_name" size="lg" />
                    @endif
                    <div class="min-w-0">
                        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ optional($visit->patient)->full_name ?? __('visits.show_heading') }}</h1>
                        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('visits.show_subtitle', ['id' => $visit->id, 'date' => $visitDateStr]) }}</p>
                        <div class="mt-2">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $visit->statusBadgeClasses() }}">
                                {{ $visit->statusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>
                @can('update', $visit)
                    <a href="{{ route('visits.edit', $visit) }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('visits.btn_edit_visit') }}</a>
                @endcan
            </div>
        </div>

        <x-visit.nav :visit="$visit" active="show" />

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90 px-4 py-4 sm:px-5">
                <h2 class="text-base font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('visits.section_visit_data') }}</h2>
            </div>
            <div class="p-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('visits.th_patient') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-[#F3F4F6] m-0">{{ optional($visit->patient)->full_name ?? __('common.em_dash') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('visits.th_visit_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0">@safeDate($visit->visit_date)</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('visits.th_doctor') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-[#F3F4F6] m-0">{{ optional($visit->doctor)->full_name ?? __('common.em_dash') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('visits.th_linked_appointment') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0 tabular-nums">
                            @if ($visit->appointment)
                                @safeDate($visit->appointment->appointment_date)
                            @else
                                {{ __('visits.no_linked_appointment') }}
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2 rounded-lg border border-[#0F4C81]/15 bg-blue-50/30 p-4 dark:bg-blue-950/20">
                        <p class="text-xs font-bold text-[#0F4C81] uppercase m-0 mb-3">{{ __('emr.soap_heading') }}</p>
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div><dt class="text-xs font-bold text-gray-400">{{ __('emr.soap_subjective') }}</dt><dd class="text-sm m-0 mt-1 whitespace-pre-wrap">{{ $soap['subjective'] ?: __('common.em_dash') }}</dd></div>
                            <div><dt class="text-xs font-bold text-gray-400">{{ __('emr.soap_objective') }}</dt><dd class="text-sm m-0 mt-1 whitespace-pre-wrap">{{ $soap['objective'] ?: __('common.em_dash') }}</dd></div>
                            <div><dt class="text-xs font-bold text-gray-400">{{ __('emr.soap_assessment') }}</dt><dd class="text-sm m-0 mt-1 whitespace-pre-wrap">{{ $soap['assessment'] ?: __('common.em_dash') }}</dd></div>
                            <div><dt class="text-xs font-bold text-gray-400">{{ __('emr.soap_plan') }}</dt><dd class="text-sm m-0 mt-1 whitespace-pre-wrap">{{ $soap['plan'] ?: __('common.em_dash') }}</dd></div>
                        </dl>
                        @if(filled($soap['notes']))
                            <p class="text-xs font-bold text-gray-400 mt-3 m-0">{{ __('emr.soap_supplemental_notes') }}</p>
                            <p class="text-sm m-0 mt-1 whitespace-pre-wrap">{{ $soap['notes'] }}</p>
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('visits.field_procedures_free') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0 whitespace-pre-wrap">{{ $visit->procedures ?? __('common.em_dash') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide">{{ __('visits.field_prescriptions_free') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-[#F3F4F6] m-0 whitespace-pre-wrap">{{ $visit->prescriptions ?? __('common.em_dash') }}</dd>
                    </div>

                    @if($visit->structuredProcedures->isNotEmpty())
                        <div class="sm:col-span-2 rounded-lg border border-slate-100 bg-slate-50/80 p-4 dark:border-[#374151] dark:bg-[#111827]/80">
                            <dt class="text-xs font-bold text-[#0F4C81] dark:text-[#93C5FD] uppercase tracking-wide m-0 mb-2">{{ __('visits.section_procedures_table') }}</dt>
                            <dd class="m-0">
                                <ul class="m-0 list-none space-y-2">
                                    @foreach($visit->structuredProcedures as $proc)
                                        <li class="text-sm text-gray-900 dark:text-[#F3F4F6]">
                                            <span class="font-semibold">{{ $proc->name }}</span>
                                            @if(filled($proc->notes))
                                                <span class="text-gray-600 dark:text-[#9CA3AF]"> — {{ $proc->notes }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif

                    @if(isset($inventoryMovements) && $inventoryMovements->isNotEmpty())
                        <div class="sm:col-span-2 rounded-lg border border-teal-100 bg-teal-50/80 p-4 dark:border-teal-900/40 dark:bg-teal-950/20">
                            <dt class="text-xs font-bold text-teal-900 dark:text-teal-200 uppercase tracking-wide m-0 mb-2">{{ __('inventory.visit_consumption_title') }}</dt>
                            <dd class="m-0">
                                <ul class="m-0 list-none space-y-1 text-sm">
                                    @foreach($inventoryMovements as $mov)
                                        <li class="flex justify-between gap-2 text-gray-900 dark:text-[#F3F4F6]">
                                            <span>{{ $mov->item?->name ?? '—' }}</span>
                                            <span class="tabular-nums font-bold">−{{ number_format((float) $mov->quantity, 3) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @canany(['manage inventory', 'view inventory'])
                                    <a href="{{ route('inventory.movements.index') }}" data-spa class="mt-2 inline-block text-xs font-bold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('inventory.link_inventory') }}</a>
                                @endcan
                            </dd>
                        </div>
                    @endif

                    @if($visit->structuredPrescriptions->isNotEmpty())
                        <div class="sm:col-span-2 rounded-lg border border-slate-100 bg-slate-50/80 p-4 dark:border-[#374151] dark:bg-[#111827]/80">
                            <dt class="text-xs font-bold text-[#0F4C81] dark:text-[#93C5FD] uppercase tracking-wide m-0 mb-2">{{ __('visits.section_rx_table') }}</dt>
                            <dd class="m-0 overflow-x-auto">
                                <table class="w-full min-w-[560px] text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 dark:border-[#374151]">
                                            <th class="py-2 ps-3 pe-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('visits.th_rx_medication') }}</th>
                                            <th class="py-2 ps-3 pe-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('visits.th_rx_dosage') }}</th>
                                            <th class="py-2 ps-3 pe-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('visits.th_rx_frequency') }}</th>
                                            <th class="py-2 ps-3 pe-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('visits.th_rx_duration') }}</th>
                                            <th class="py-2 ps-3 pe-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB]">{{ __('visits.th_rx_notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($visit->structuredPrescriptions as $rx)
                                            <tr class="border-b border-slate-100 dark:border-gray-800 align-top">
                                                <td class="py-2 ps-3 pe-3 text-gray-900 dark:text-[#F3F4F6]">{{ $rx->medication_name }}</td>
                                                <td class="py-2 ps-3 pe-3 text-gray-700 dark:text-[#E5E7EB]">{{ $rx->dosage ?: __('common.em_dash') }}</td>
                                                <td class="py-2 ps-3 pe-3 text-gray-700 dark:text-[#E5E7EB]">{{ $rx->frequency ?: __('common.em_dash') }}</td>
                                                <td class="py-2 ps-3 pe-3 text-gray-700 dark:text-[#E5E7EB]">{{ $rx->duration ?: __('common.em_dash') }}</td>
                                                <td class="py-2 ps-3 pe-3 text-gray-600 dark:text-[#9CA3AF]">{{ $rx->notes ?: __('common.em_dash') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </dd>
                        </div>
                    @endif

                    @if($visit->notes)
                        <div class="sm:col-span-2 border-t border-gray-100 pt-4 dark:border-[#374151]">
                            <dt class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide m-0 mb-1">{{ __('visits.field_notes') }}</dt>
                            <dd class="text-sm text-gray-700 dark:text-[#E5E7EB] m-0 whitespace-pre-wrap">{{ $visit->notes }}</dd>
                        </div>
                    @endif
                </dl>
                @if($visit->patient)
                    <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-[#374151]">
                        <a href="{{ route('patients.show', $visit->patient) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-[#0F4C81] hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#93C5FD] dark:hover:bg-[#374151]">{{ __('visits.link_patient_file') }}</a>
                    </div>
                @endif
            </div>
        </div>

        @include('partials.attachments-card', [
            'attachments' => $attachments,
            'uploadUrl' => route('visits.attachments.store', $visit),
        ])
    </div>
