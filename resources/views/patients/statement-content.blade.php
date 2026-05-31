@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $qBase = array_merge(['patient' => $patient->id], request()->query());
    $patientDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
<div class="max-w-[1200px] mx-auto" dir="{{ $patientDir }}">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('patients.statement_heading') }}</h1>
            <p class="mt-2 text-slate-600 dark:text-[#9CA3AF] m-0">{{ $patient->full_name }} — {{ $reportMeta['date_range_label'] }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('patients.profile', array_merge(['patient' => $patient->id], request()->query())) }}" data-spa class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('patients.statement_profile_link') }}</a>
            <a href="{{ route('patients.statement.print', $qBase) }}" target="_blank" rel="noopener" class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-800">{{ __('common.print') }}</a>
            <a href="{{ route('patients.statement.pdf', $qBase) }}" class="inline-flex rounded-lg border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-bold text-[#0F4C81]">{{ __('common.pdf') }}</a>
            <a href="{{ route('patients.statement.excel', $qBase) }}" target="_blank" rel="noopener" class="inline-flex rounded-lg border border-[#1F7A8C] bg-white px-4 py-2.5 text-sm font-bold text-[#1F7A8C]">{{ __('common.excel') }}</a>
        </div>
    </div>

    <x-patient.nav :patient="$patient" active="statement" />

    <form method="get" action="{{ route('patients.statement', $patient) }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-[#374151] dark:bg-[#1F2937]">
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
            <select name="doctor_id" class="mt-1 min-w-[180px] rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white">
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
        <button type="submit" class="inline-flex rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('patients.apply') }}</button>
    </form>

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs text-slate-500">{{ __('patients.statement_total_invoiced_debit') }}</p>
            <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($statementTotals['total_invoiced'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-xl border border-teal-200 bg-teal-50/60 p-4 dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs text-teal-900 dark:text-teal-200">{{ __('patients.statement_total_paid_credit') }}</p>
            <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($statementTotals['total_paid'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
            <p class="m-0 text-xs text-rose-900 dark:text-rose-200">{{ __('patients.statement_ending_balance') }}</p>
            <p class="mt-1 mb-0 text-lg font-bold tabular-nums">{{ number_format($statementTotals['ending_balance'], 2) }} {{ $cur }}</p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <table class="min-w-full text-start text-sm">
            <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600 dark:bg-[#111827] dark:text-[#94A3B8]">
            <tr>
                <th class="px-4 py-3">{{ __('patients.col_date') }}</th>
                <th class="px-4 py-3">{{ __('patients.ledger_col_type') }}</th>
                <th class="px-4 py-3">{{ __('patients.ledger_col_ref') }}</th>
                <th class="px-4 py-3">{{ __('patients.ledger_col_description') }}</th>
                <th class="px-4 py-3">{{ __('patients.ledger_col_debit') }}</th>
                <th class="px-4 py-3">{{ __('patients.ledger_col_credit') }}</th>
                <th class="px-4 py-3">{{ __('patients.ledger_col_balance') }}</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
            @forelse($ledger as $row)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $row['date'] }}</td>
                    <td class="px-4 py-3">{{ $row['type'] }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $row['ref'] }}</td>
                    <td class="px-4 py-3">{{ $row['description'] }}</td>
                    <td class="px-4 py-3 tabular-nums text-rose-700 dark:text-rose-300">{{ $row['debit'] ?? __('patients.em_dash') }}</td>
                    <td class="px-4 py-3 tabular-nums text-emerald-700 dark:text-emerald-300">{{ $row['credit'] ?? __('patients.em_dash') }}</td>
                    <td class="px-4 py-3 tabular-nums font-bold">{{ $row['balance'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ __('patients.empty_ledger') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
