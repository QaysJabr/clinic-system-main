@php
    $clinic = \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $pmLabels = \App\Support\PaymentMethods::options();
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $expenseStats ?? ['today' => 0, 'month_total' => 0, 'unpaid' => 0, 'installments' => 0];
    $hasFilters = request()->filled('expense_category_id') || request()->filled('date_from') || request()->filled('date_to')
        || request()->filled('payment_method') || request()->filled('q') || request()->filled('settlement_type') || request()->boolean('unpaid_only');
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd = now()->endOfMonth()->toDateString();
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('expenses.page_title_index') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('expenses.subtitle_index') }}</p>
    </div>

    <x-expense.nav active="list" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('expenses.stat_today') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('expenses.stat_month_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['month_total'], 2) }}@if(filled($cur)) <span class="text-sm font-semibold text-slate-500">{{ $cur }}</span>@endif</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('expenses.stat_unpaid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['unpaid']) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200/90 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-indigo-800 dark:text-indigo-300">{{ __('expenses.stat_installments') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-indigo-900 dark:text-indigo-200">{{ number_format($stats['installments']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('expenses.index', ['date_from' => $monthStart, 'date_to' => $monthEnd]) }}" data-spa class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('date_from') === $monthStart && request('date_to') === $monthEnd ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('expenses.quick_this_month') }}</a>
        <a href="{{ route('expenses.index', ['unpaid_only' => 1]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request()->boolean('unpaid_only') ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
            {{ __('expenses.quick_unpaid') }}
        </a>
        <a href="{{ route('expenses.index', ['settlement_type' => \App\Models\Expense::SETTLEMENT_INSTALLMENTS]) }}" data-spa class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('settlement_type') === \App\Models\Expense::SETTLEMENT_INSTALLMENTS ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('expenses.badge_installments') }}</a>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('expenses.filter_results') }}</p>
        <form method="GET" action="{{ route('expenses.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.filter_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-3">
                        <label for="expense_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_category') }}</label>
                        <select name="expense_category_id" id="expense_category_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('expenses.filter_all') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) request('expense_category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_date_from') }}</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_date_to') }}</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="payment_method" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.payment_method_filter') }}</label>
                        <select name="payment_method" id="payment_method"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('expenses.filter_all') }}</option>
                            @foreach (array_keys($pmLabels) as $pmCode)
                                <option value="{{ $pmCode }}" @selected(request('payment_method') === $pmCode)>{{ $pmLabels[$pmCode] ?? $pmCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label for="q" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_search_title_notes') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('expenses.search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-12 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('expenses.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('expenses.list_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.table_heading_all') }}</h2>
                    @if ($expenses->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('expenses.results_count', [
                                'from' => $expenses->firstItem(),
                                'to' => $expenses->lastItem(),
                                'total' => $expenses->total(),
                            ]) }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.th_title') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.th_amount_paid') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.th_date') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.field_status') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('expenses.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            @php
                                $paidSum = (float) ($expense->payments_sum_amount ?? 0);
                                $settlementStatus = $expense->settlementStatusFromPaid($paidSum);
                            @endphp
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('expenses.show', $expense) }}" data-spa class="group block min-w-0">
                                        <span class="flex min-w-0 items-center gap-2">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white dark:ring-[#1F2937]" style="background: {{ $expense->settlementStatusColor($paidSum) }}" aria-hidden="true"></span>
                                            <span class="min-w-0 font-semibold text-gray-900 transition group-hover:text-[#0F4C81] dark:text-[#F3F4F6] dark:group-hover:text-[#93C5FD]">{{ $expense->title }}</span>
                                        </span>
                                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ optional($expense->category)->name ?? __('common.em_dash') }} · {{ $expense->documentNumber() }}</span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 tabular-nums sm:px-5">
                                    @if ($expense->isInstallments())
                                        <span class="block font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ number_format($paidSum, 2) }} / {{ number_format((float) $expense->amount, 2) }}</span>
                                        @if (filled($cur))<span class="text-xs text-slate-500">{{ $cur }}</span>@endif
                                    @else
                                        <span class="block font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ number_format((float) $expense->amount, 2) }}@if(filled($cur)) <span class="text-xs text-slate-500">{{ $cur }}</span>@endif</span>
                                    @endif
                                    <span class="mt-0.5 block text-xs text-slate-500">{{ $pmLabels[$expense->payment_method] ?? $expense->payment_method }}</span>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $expense->expense_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <x-expense.status-badge :status="$settlementStatus" />
                                    @if ($expense->isInstallments())
                                        <span class="ms-1 text-[10px] font-bold uppercase text-indigo-600 dark:text-indigo-300">{{ __('expenses.badge_installments') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <a href="{{ route('expenses.show', $expense) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('expenses.action_show') }}</a>
                                        <a href="{{ route('expenses.edit', $expense) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('expenses.action_edit') }}</a>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" data-confirm-title="{{ __('expenses.confirm_delete_title') }}" data-confirm="{{ __('expenses.confirm_delete_body') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-red-600 hover:underline dark:text-red-400">{{ __('common.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-gray-700 dark:text-gray-200">{{ __('expenses.empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $hasFilters ? __('expenses.no_matching') : __('expenses.empty_hint') }}</p>
                                    @if (! $hasFilters)
                                        @can('manage expenses')
                                            <a href="{{ route('expenses.create') }}" data-spa class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('expenses.btn_add') }}</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($expenses->hasPages())
                <div class="border-t border-gray-100 bg-[#FAFBFC] px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/40 sm:px-5">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
