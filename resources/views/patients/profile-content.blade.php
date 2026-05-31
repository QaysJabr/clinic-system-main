@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $qBase = array_merge(['patient' => $patient->id], request()->query());
    $patientDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $invStatusLabel = fn ($s) => match ($s) {
        'paid' => __('patients.invoice_status_paid'),
        'partial' => __('patients.invoice_status_partial'),
        default => __('patients.invoice_status_unpaid'),
    };
    $apptStatusLabel = fn ($s) => match ($s) {
        'scheduled' => __('patients.appointment_status_scheduled'),
        'completed' => __('patients.appointment_status_completed'),
        'cancelled' => __('patients.appointment_status_cancelled'),
        default => (string) $s,
    };
    $genderLabel = match ($patient->gender) {
        'male', 'm' => __('patients.gender_male'),
        'female', 'f' => __('patients.gender_female'),
        default => $patient->gender ? (string) $patient->gender : __('patients.em_dash'),
    };
    $lastVisitLabel = ! empty($summary['last_visit_date'])
        ? \Illuminate\Support\Carbon::parse($summary['last_visit_date'])->format('d/m/Y')
        : __('patients.em_dash');
    $profileHasFilters = request()->filled('date_from')
        || request()->filled('date_to')
        || request()->filled('doctor_id')
        || request()->filled('invoice_status')
        || request()->filled('visit_status')
        || request()->filled('appointment_status');
@endphp
<div class="max-w-[1400px] mx-auto" dir="{{ $patientDir }}" x-data="{ tab: 'overview' }">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-start lg:justify-between">
        <div>
            <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('patients.profile_heading') }}</h1>
            <p class="text-gray-600 dark:text-[#9CA3AF] text-sm mt-2 m-0">{{ $patient->full_name }} — {{ $patient->file_number }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 m-0">{{ __('patients.period_label', ['range' => $reportMeta['date_range_label'] ?? __('patients.em_dash')]) }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @can('manage visits')
                <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}" data-spa class="inline-flex items-center justify-center rounded-xl bg-[#1F7A8C] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#196878]">{{ __('patients.add_visit') }}</a>
            @endcan
            @can('manage invoices')
                <a href="{{ route('invoices.create', ['patient_id' => $patient->id]) }}" data-spa class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('patients.add_invoice') }}</a>
            @endcan
            <a href="{{ route('patients.profile.print', $qBase) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('patients.print_profile') }}</a>
            <a href="{{ route('patients.profile.pdf', $qBase) }}" class="inline-flex items-center justify-center rounded-xl border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-bold text-[#0F4C81] dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD]">{{ __('patients.pdf_profile') }}</a>
        </div>
    </div>

    <x-patient.nav :patient="$patient" active="profile" />

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('patients.section_filter_results') }}</p>
        <form method="get" action="{{ route('patients.profile', $patient) }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
            <div>
                <label class="block text-xs font-bold text-slate-500">{{ __('patients.label_date_from') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500">{{ __('patients.label_date_to') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500">{{ __('patients.label_doctor') }}</label>
                <select name="doctor_id" class="mt-1 min-w-[160px] rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
                    <option value="">{{ __('patients.filter_all') }}</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" @selected((string) request('doctor_id') === (string) $d->id)>{{ $d->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500">{{ __('patients.label_invoice_status') }}</label>
                <select name="invoice_status" class="mt-1 min-w-[140px] rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
                    <option value="">{{ __('patients.filter_all') }}</option>
                    <option value="unpaid" @selected(request('invoice_status') === 'unpaid')>{{ __('patients.invoice_status_unpaid') }}</option>
                    <option value="partial" @selected(request('invoice_status') === 'partial')>{{ __('patients.invoice_status_partial') }}</option>
                    <option value="paid" @selected(request('invoice_status') === 'paid')>{{ __('patients.invoice_status_paid') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500">{{ __('patients.label_visit_status') }}</label>
                <select name="visit_status" class="mt-1 min-w-[150px] rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
                    <option value="">{{ __('patients.filter_all') }}</option>
                    <option value="{{ \App\Models\Visit::STATUS_WAITING }}" @selected(request('visit_status') === \App\Models\Visit::STATUS_WAITING)>{{ __('visits.status_waiting') }}</option>
                    <option value="{{ \App\Models\Visit::STATUS_IN_PROGRESS }}" @selected(request('visit_status') === \App\Models\Visit::STATUS_IN_PROGRESS)>{{ __('visits.status_in_progress') }}</option>
                    <option value="{{ \App\Models\Visit::STATUS_COMPLETED }}" @selected(request('visit_status') === \App\Models\Visit::STATUS_COMPLETED)>{{ __('visits.status_completed') }}</option>
                    <option value="{{ \App\Models\Visit::STATUS_CANCELLED }}" @selected(request('visit_status') === \App\Models\Visit::STATUS_CANCELLED)>{{ __('visits.status_cancelled') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500">{{ __('patients.label_appointment_status') }}</label>
                <select name="appointment_status" class="mt-1 min-w-[140px] rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
                    <option value="">{{ __('patients.filter_all') }}</option>
                    <option value="scheduled" @selected(request('appointment_status') === 'scheduled')>{{ __('patients.appointment_status_scheduled') }}</option>
                    <option value="completed" @selected(request('appointment_status') === 'completed')>{{ __('patients.appointment_status_completed') }}</option>
                    <option value="cancelled" @selected(request('appointment_status') === 'cancelled')>{{ __('patients.appointment_status_cancelled') }}</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('patients.apply_filters') }}</button>
                @if ($profileHasFilters)
                    <a href="{{ route('patients.profile', $patient) }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-slate-200">{{ __('patients.reset') }}</a>
                @endif
            </div>
            </div>
        </form>
    </div>

    <div class="mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2 dark:border-[#374151]">
        <button type="button" @click="tab='overview'" :class="tab==='overview' ? 'bg-[#0F4C81] text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#374151] dark:text-[#E5E7EB]'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ __('patients.tab_overview') }}</button>
        <button type="button" @click="tab='visits'" :class="tab==='visits' ? 'bg-[#0F4C81] text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#374151] dark:text-[#E5E7EB]'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ __('patients.tab_visits') }}</button>
        <button type="button" @click="tab='appointments'" :class="tab==='appointments' ? 'bg-[#0F4C81] text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#374151] dark:text-[#E5E7EB]'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ __('patients.tab_appointments') }}</button>
        <button type="button" @click="tab='invoices'" :class="tab==='invoices' ? 'bg-[#0F4C81] text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#374151] dark:text-[#E5E7EB]'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ __('patients.tab_invoices') }}</button>
        <button type="button" @click="tab='payments'" :class="tab==='payments' ? 'bg-[#0F4C81] text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#374151] dark:text-[#E5E7EB]'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ __('patients.tab_payments') }}</button>
        <button type="button" @click="tab='statement'" :class="tab==='statement' ? 'bg-[#0F4C81] text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#374151] dark:text-[#E5E7EB]'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ __('patients.tab_statement') }}</button>
    </div>

    <div x-show="tab==='overview'" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-xs text-slate-500">{{ __('patients.summary_invoice_total') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($summary['invoice_total'], 2) }} {{ $cur }}</p>
            </div>
            <div class="rounded-xl border border-teal-200 bg-teal-50/50 p-4 dark:border-teal-900/40 dark:bg-teal-950/20">
                <p class="m-0 text-xs text-teal-900 dark:text-teal-200">{{ __('patients.summary_paid') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($summary['paid_total'], 2) }} {{ $cur }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
                <p class="m-0 text-xs text-rose-900 dark:text-rose-200">{{ __('patients.summary_balance') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($summary['balance'], 2) }} {{ $cur }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-xs text-slate-500">{{ __('patients.summary_visit_count') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ $summary['visit_count'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-xs text-slate-500">{{ __('patients.summary_invoice_count') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ $summary['invoice_count'] }}</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-4 dark:border-indigo-900/40 dark:bg-indigo-950/20">
                <p class="m-0 text-xs text-indigo-900 dark:text-indigo-200">{{ __('patients.summary_last_visit') }}</p>
                <p class="mt-1 mb-0 text-base font-bold">{{ $summary['last_visit_date'] ? \Illuminate\Support\Carbon::parse($summary['last_visit_date'])->format('d/m/Y') : __('patients.em_dash') }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <h2 class="m-0 text-base font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('patients.patient_demographics') }}</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2 dark:border-[#374151]"><dt class="text-slate-500 m-0">{{ __('patients.field_name') }}</dt><dd class="font-semibold m-0">{{ $patient->full_name }}</dd></div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2 dark:border-[#374151]"><dt class="text-slate-500 m-0">{{ __('patients.field_phone') }}</dt><dd class="m-0">{{ $patient->phone ?: __('patients.em_dash') }}</dd></div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2 dark:border-[#374151]"><dt class="text-slate-500 m-0">{{ __('patients.field_date_of_birth') }}</dt><dd class="m-0">@safeDate($patient->date_of_birth) @if($patient->date_of_birth)<span class="text-slate-400">({{ __('patients.age_years', ['count' => $patient->date_of_birth->age]) }})</span>@endif</dd></div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2 dark:border-[#374151]"><dt class="text-slate-500 m-0">{{ __('patients.field_gender') }}</dt><dd class="m-0">{{ $genderLabel }}</dd></div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2 dark:border-[#374151] sm:col-span-2"><dt class="text-slate-500 m-0">{{ __('patients.field_address') }}</dt><dd class="m-0 text-start sm:text-end">{{ $patient->address ?: __('patients.em_dash') }}</dd></div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2 dark:border-[#374151]"><dt class="text-slate-500 m-0">{{ __('patients.field_registration_date') }}</dt><dd class="m-0">@safeDate($patient->created_at)</dd></div>
            </dl>
        </div>
    </div>

    <div x-show="tab==='visits'" x-cloak>
        @if(! $canViewClinical)
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/25 dark:text-amber-100">{{ __('patients.no_clinical_visits_permission') }}</p>
        @else
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
                <table class="min-w-full text-start text-sm">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                    <tr>
                        <th class="px-3 py-3">{{ __('patients.col_date') }}</th>
                        <th class="px-3 py-3">{{ __('patients.label_doctor') }}</th>
                        <th class="px-3 py-3">{{ __('patients.col_appointment') }}</th>
                        <th class="px-3 py-3">{{ __('patients.col_chief_complaint') }}</th>
                        <th class="px-3 py-3">{{ __('patients.col_diagnosis_notes') }}</th>
                        <th class="px-3 py-3">{{ __('patients.field_status') }}</th>
                        <th class="px-3 py-3">{{ __('patients.col_actions_short') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                    @forelse($visits as $v)
                        <tr>
                            <td class="px-3 py-3 whitespace-nowrap">@safeDate($v->visit_date)</td>
                            <td class="px-3 py-3">{{ $v->doctor->full_name ?? __('patients.em_dash') }}</td>
                            <td class="px-3 py-3">
                                @if($v->appointment)
                                    <span class="text-xs">@safeDate($v->appointment->appointment_date)</span>
                                @else
                                    {{ __('patients.em_dash') }}
                                @endif
                            </td>
                            <td class="px-3 py-3 max-w-[200px]">{{ \Illuminate\Support\Str::limit((string) ($v->chief_complaint ?? ''), 80) }}</td>
                            <td class="px-3 py-3 max-w-[220px]">{{ \Illuminate\Support\Str::limit(trim((string) ($v->diagnosis ?? '').' '.(string) ($v->notes ?? '')), 100) }}</td>
                            <td class="px-3 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $v->statusBadgeClasses() }}">{{ $v->statusLabel() }}</span></td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @can('view', $v)
                                    <a href="{{ route('visits.show', $v) }}" data-spa class="text-[#0F4C81] font-semibold">{{ __('patients.view') }}</a>
                                @endcan
                                @can('update', $v)
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('visits.edit', $v) }}" data-spa class="text-[#1F7A8C] font-semibold">{{ __('patients.edit') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-slate-500">{{ __('patients.empty_visits_filtered') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $visits->links() }}</div>
        @endif
    </div>

    <div x-show="tab==='appointments'" x-cloak>
        @if(! $canViewClinical)
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/25 dark:text-amber-100">{{ __('patients.no_clinical_appointments_permission') }}</p>
        @else
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
                <table class="min-w-full text-start text-sm">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                    <tr>
                        <th class="px-3 py-3">{{ __('patients.col_datetime') }}</th>
                        <th class="px-3 py-3">{{ __('patients.label_doctor') }}</th>
                        <th class="px-3 py-3">{{ __('patients.field_status') }}</th>
                        <th class="px-3 py-3">{{ __('patients.field_notes') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                    @forelse($appointments as $a)
                        <tr>
                            <td class="px-3 py-3 whitespace-nowrap">@safeDate($a->appointment_date) {{ $a->start_time ? ' — '.$a->start_time : '' }}</td>
                            <td class="px-3 py-3">{{ $a->doctor->full_name ?? __('patients.em_dash') }}</td>
                            <td class="px-3 py-3">{{ $apptStatusLabel($a->status) }}</td>
                            <td class="px-3 py-3 max-w-xs">{{ \Illuminate\Support\Str::limit((string) ($a->notes ?? ''), 120) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">{{ __('patients.empty_appointments_filtered') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $appointments->links() }}</div>
        @endif
    </div>

    <div x-show="tab==='invoices'" x-cloak>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                <tr>
                    <th class="px-3 py-3">{{ __('patients.col_invoice_number') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_date') }}</th>
                    <th class="px-3 py-3">{{ __('patients.label_doctor') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_total') }}</th>
                    <th class="px-3 py-3">{{ __('patients.summary_paid') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_remaining') }}</th>
                    <th class="px-3 py-3">{{ __('patients.field_status') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_actions_short') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                @forelse($invoices as $inv)
                    @php $rem = (float) $inv->total - (float) $inv->paid; @endphp
                    <tr>
                        <td class="px-3 py-3 font-semibold">{{ $inv->invoice_number }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">@safeDate($inv->created_at)</td>
                        <td class="px-3 py-3">{{ $inv->treatingDoctor->full_name ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-3 tabular-nums">{{ number_format((float) $inv->total, 2) }}</td>
                        <td class="px-3 py-3 tabular-nums">{{ number_format((float) $inv->paid, 2) }}</td>
                        <td class="px-3 py-3 tabular-nums font-semibold">{{ number_format($rem, 2) }}</td>
                        <td class="px-3 py-3">{{ $invStatusLabel($inv->status) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @can('manage invoices')
                                <a href="{{ route('invoices.show', $inv) }}" data-spa class="text-[#0F4C81] font-semibold">{{ __('patients.view') }}</a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('invoices.print', $inv) }}" target="_blank" rel="noopener" class="text-[#1F7A8C] font-semibold">{{ __('common.print') }}</a>
                            @endcan
                            @cannot('manage invoices')
                                {{ __('patients.em_dash') }}
                            @endcannot
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-8 text-center text-slate-500">{{ __('patients.empty_invoices_filtered') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>

    <div x-show="tab==='payments'" x-cloak>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                <tr>
                    <th class="px-3 py-3">{{ __('patients.col_payment_date') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_invoice_number') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_amount') }}</th>
                    <th class="px-3 py-3">{{ __('patients.col_payment_method') }}</th>
                    <th class="px-3 py-3">{{ __('patients.field_notes') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                @forelse($payments as $pay)
                    <tr>
                        <td class="px-3 py-3 whitespace-nowrap">@safeDate($pay->payment_date)</td>
                        <td class="px-3 py-3 font-medium">{{ $pay->invoice->invoice_number ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-3 tabular-nums font-bold">{{ number_format((float) $pay->amount, 2) }} {{ $cur }}</td>
                        <td class="px-3 py-3">{{ $pay->payment_method ?: __('patients.em_dash') }}</td>
                        <td class="px-3 py-3 max-w-xs">{{ \Illuminate\Support\Str::limit((string) ($pay->notes ?? ''), 80) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">{{ __('patients.empty_payments_filtered') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    </div>

    <div x-show="tab==='statement'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-[#374151] dark:bg-[#1F2937]">
                <p class="m-0 text-xs text-slate-500">{{ __('patients.statement_total_invoiced_debit') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($statementTotals['total_invoiced'], 2) }} {{ $cur }}</p>
            </div>
            <div class="rounded-xl border border-teal-200 bg-teal-50/50 p-4 dark:border-teal-900/40 dark:bg-teal-950/20">
                <p class="m-0 text-xs text-teal-900 dark:text-teal-200">{{ __('patients.statement_total_paid_credit') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($statementTotals['total_paid'], 2) }} {{ $cur }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
                <p class="m-0 text-xs text-rose-900 dark:text-rose-200">{{ __('patients.statement_ending_balance') }}</p>
                <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($statementTotals['ending_balance'], 2) }} {{ $cur }}</p>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
                <tr>
                    <th class="px-3 py-3">{{ __('patients.col_date') }}</th>
                    <th class="px-3 py-3">{{ __('patients.ledger_col_type') }}</th>
                    <th class="px-3 py-3">{{ __('patients.ledger_col_ref') }}</th>
                    <th class="px-3 py-3">{{ __('patients.ledger_col_description') }}</th>
                    <th class="px-3 py-3">{{ __('patients.ledger_col_debit') }}</th>
                    <th class="px-3 py-3">{{ __('patients.ledger_col_credit') }}</th>
                    <th class="px-3 py-3">{{ __('patients.ledger_col_balance') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                @forelse($ledger as $row)
                    <tr>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $row['date'] }}</td>
                        <td class="px-3 py-3">{{ $row['type'] }}</td>
                        <td class="px-3 py-3 tabular-nums">{{ $row['ref'] }}</td>
                        <td class="px-3 py-3">{{ $row['description'] }}</td>
                        <td class="px-3 py-3 tabular-nums text-rose-700 dark:text-rose-300">{{ $row['debit'] ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-3 tabular-nums text-emerald-700 dark:text-emerald-300">{{ $row['credit'] ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-3 tabular-nums font-bold">{{ $row['balance'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-8 text-center text-slate-500">{{ __('patients.empty_ledger') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
