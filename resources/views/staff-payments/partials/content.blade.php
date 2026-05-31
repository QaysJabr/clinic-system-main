@php
    $clinic = \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $pmLabels = \App\Support\PaymentMethods::options();
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $paymentStats ?? ['month_paid' => 0, 'month_due' => 0, 'outstanding' => 0, 'month_count' => 0];
    $hasFilters = request()->filled('staff_id') || request()->filled('role_type') || request()->filled('filter_period_type')
        || request()->filled('filter_period_from') || request()->filled('filter_period_to') || request()->filled('payment_status');
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd = now()->endOfMonth()->toDateString();
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('payroll.page_title_index') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('payroll.subtitle_index') }}</p>
        </div>
        @can('manage payroll')
            <a href="{{ route('staff-payments.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('payroll.btn_add_record') }}</a>
        @endcan
    </div>

    <x-staff.nav active="payments" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('payroll.stat_month_paid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">
                {{ number_format($stats['month_paid'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('payroll.stat_month_due') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">
                {{ number_format($stats['month_due'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('payroll.stat_outstanding') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['outstanding']) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200/90 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-indigo-800 dark:text-indigo-300">{{ __('payroll.stat_month_records') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-indigo-900 dark:text-indigo-200">{{ number_format($stats['month_count']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('staff-payments.index', ['filter_period_from' => $monthStart, 'filter_period_to' => $monthEnd]) }}" data-spa class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('filter_period_from') === $monthStart && request('filter_period_to') === $monthEnd ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('payroll.quick_this_month') }}</a>
        <a href="{{ route('staff-payments.index', ['payment_status' => 'unpaid']) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('payment_status') === 'unpaid' ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-rose-500" aria-hidden="true"></span>
            {{ __('payroll.status_unpaid') }}
        </a>
        <a href="{{ route('staff-payments.index', ['payment_status' => 'partial']) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('payment_status') === 'partial' ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
            {{ __('payroll.status_partial') }}
        </a>
        @can('manage staff payroll')
            <a href="{{ route('staff-compensation-profiles.index') }}" data-spa class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300">{{ __('payroll.link_compensation_profiles') }}</a>
        @endcan
        <a href="{{ route('payroll-runs.index') }}" data-spa class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300">{{ __('payroll.link_payroll_runs') }}</a>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('staff.section_filter') }}</p>
        <form method="GET" action="{{ route('staff-payments.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.section_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-3">
                        <label for="staff_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.th_staff') }}</label>
                        <select name="staff_id" id="staff_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('expenses.filter_all') }}</option>
                            @foreach ($staffList as $s)
                                <option value="{{ $s->id }}" @selected((string) request('staff_id') === (string) $s->id)>{{ $s->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="role_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.filter_role') }}</label>
                        <select name="role_type" id="role_type"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('staff.filter_all_roles') }}</option>
                            @foreach ($roleTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('role_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="filter_period_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.filter_period_type') }}</label>
                        <select name="filter_period_type" id="filter_period_type"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('staff.filter_all_status') }}</option>
                            @foreach ([\App\Enums\PaymentCycle::Weekly, \App\Enums\PaymentCycle::Monthly] as $cycle)
                                <option value="{{ $cycle->value }}" @selected(request('filter_period_type') === $cycle->value)>{{ $cycle->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="filter_period_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.filter_period_from') }}</label>
                        <input type="date" name="filter_period_from" id="filter_period_from" value="{{ request('filter_period_from') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="filter_period_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.filter_period_to') }}</label>
                        <input type="date" name="filter_period_to" id="filter_period_to" value="{{ request('filter_period_to') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="xl:col-span-1">
                        <label for="payment_status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.th_status') }}</label>
                        <select name="payment_status" id="payment_status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('staff.filter_all_status') }}</option>
                            <option value="completed" @selected(request('payment_status') === 'completed')>{{ __('payroll.status_completed') }}</option>
                            <option value="partial" @selected(request('payment_status') === 'partial')>{{ __('payroll.status_partial') }}</option>
                            <option value="unpaid" @selected(request('payment_status') === 'unpaid')>{{ __('payroll.status_unpaid') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-12 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('staff.btn_search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('staff-payments.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('staff.btn_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('payroll.list_section_label') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('payroll.table_main_heading') }}</h2>
                @if ($payments->total() > 0)
                    <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('staff.results_count', ['from' => $payments->firstItem(), 'to' => $payments->lastItem(), 'total' => $payments->total()]) }}
                    </p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1200px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_staff_full_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_period') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_status') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_base') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_bonus') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_deduction') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_total_due') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_paid') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_remaining') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_pay_date') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.th_payment_method') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('staff-payments.edit', $payment) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ optional($payment->staff)->full_name ?? __('common.em_dash') }}</a>
                                    <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ optional($payment->staff)->roleTypeLabel() ?? __('common.em_dash') }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-[#E5E7EB] sm:px-5">
                                    <span class="font-medium">{{ $payment->period_type->label() }}</span>
                                    <p class="m-0 mt-0.5 text-xs tabular-nums text-slate-500 dark:text-slate-400">{{ $payment->period_start?->format('d/m/Y') }} → {{ $payment->period_end?->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-4 py-3 sm:px-5"><x-staff.payment-status-badge :payment="$payment" /></td>
                                <td class="px-4 py-3 text-end tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ number_format((float) $payment->base_amount, 2) }}</td>
                                <td class="px-4 py-3 text-end tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ number_format((float) $payment->bonus, 2) }}</td>
                                <td class="px-4 py-3 text-end tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ number_format((float) $payment->deduction, 2) }}</td>
                                <td class="px-4 py-3 text-end tabular-nums font-semibold text-gray-900 dark:text-[#F3F4F6] sm:px-5">{{ number_format((float) $payment->total_due, 2) }}</td>
                                <td class="px-4 py-3 text-end tabular-nums font-medium text-emerald-700 dark:text-emerald-400 sm:px-5">{{ number_format((float) $payment->paid_amount, 2) }}</td>
                                <td class="px-4 py-3 text-end tabular-nums font-semibold {{ (float) $payment->remaining_amount > 0.01 ? 'text-amber-800 dark:text-amber-300' : 'text-slate-400' }} sm:px-5">{{ number_format((float) $payment->remaining_amount, 2) }}</td>
                                <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ $payment->payment_date?->format('d/m/Y') ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 sm:px-5">{{ $payment->payment_method ? ($pmLabels[$payment->payment_method] ?? $payment->payment_method) : __('common.em_dash') }}</td>
                                <td class="px-4 py-3 text-end whitespace-nowrap sm:px-5">
                                    <a href="{{ route('staff-payments.edit', $payment) }}" data-spa class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-[#5EEAD4]">{{ __('common.edit') }}</a>
                                    <form method="POST" action="{{ route('staff-payments.destroy', $payment) }}" class="ms-3 inline" data-confirm-title="{{ __('payroll.confirm_delete_record_title') }}" data-confirm="{{ __('payroll.confirm_delete_record_body') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:underline dark:text-red-400">{{ __('common.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-14 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-slate-600 dark:text-slate-300">{{ __('payroll.empty_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('payroll.no_matching_records') }}</p>
                                    @can('manage payroll')
                                        <a href="{{ route('staff-payments.create') }}" data-spa class="mt-4 inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('payroll.btn_add_record') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="border-t border-gray-100 px-4 py-4 dark:border-[#374151] sm:px-5">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</div>
