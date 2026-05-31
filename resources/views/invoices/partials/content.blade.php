@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $invoiceStats ?? ['today' => 0, 'unpaid' => 0, 'partial' => 0, 'outstanding' => 0];
    $hasFilters = request()->filled('invoice_number') || request()->filled('patient') || request()->filled('status')
        || request()->filled('doctor_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('date');
    $todayDate = now()->toDateString();
    $statusColors = \App\Models\Invoice::statusColors();
@endphp
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('invoices.title') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('invoices.subtitle_index') }}</p>
    </div>

    <x-invoice.nav active="list" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('invoices.stat_today') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="rounded-xl border border-red-200/90 bg-red-50/50 p-4 shadow-sm dark:border-red-900/40 dark:bg-red-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-red-800 dark:text-red-300">{{ __('invoices.stat_unpaid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-red-900 dark:text-red-200">{{ number_format($stats['unpaid']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('invoices.stat_partial') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['partial']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('invoices.stat_outstanding') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['outstanding'], 2) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('invoices.index', ['date' => $todayDate]) }}" data-spa class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('date') === $todayDate ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('invoices.quick_today') }}</a>
        @foreach (['unpaid', 'partial', 'paid'] as $quickStatus)
            <a href="{{ route('invoices.index', ['status' => $quickStatus]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('status') === $quickStatus ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
                <span class="h-2 w-2 rounded-full" style="background: {{ $statusColors[$quickStatus] ?? '#94A3B8' }}"></span>
                {{ __('common.'.$quickStatus) }}
            </a>
        @endforeach
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('invoices.filter_results') }}</p>
        <form method="GET" action="{{ route('invoices.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('invoices.filter_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-2">
                        <label for="invoice_number" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_invoice_number') }}</label>
                        <input type="search" name="invoice_number" id="invoice_number" value="{{ request('invoice_number') }}" placeholder="{{ __('invoices.search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="patient" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_patient_name') }}</label>
                        <input type="search" name="patient" id="patient" value="{{ request('patient') }}" placeholder="{{ __('invoices.search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_status') }}</label>
                        <select name="status" id="status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('invoices.status_option_all') }}</option>
                            <option value="unpaid" @selected(request('status') === 'unpaid')>{{ __('common.unpaid') }}</option>
                            <option value="partial" @selected(request('status') === 'partial')>{{ __('common.partial') }}</option>
                            <option value="paid" @selected(request('status') === 'paid')>{{ __('common.paid') }}</option>
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="doctor_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_doctor') }}</label>
                        <select name="doctor_id" id="doctor_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('invoices.status_option_all') }}</option>
                            @foreach (($doctors ?? collect()) as $doc)
                                <option value="{{ $doc->id }}" @selected((string) request('doctor_id') === (string) $doc->id)>{{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_date_from') }}</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('invoices.field_date_to') }}</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-12 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('invoices.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('invoices.list_section') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('invoices.table_heading_all') }}</h2>
                    @if ($invoices->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('invoices.results_count', [
                                'from' => $invoices->firstItem(),
                                'to' => $invoices->lastItem(),
                                'total' => $invoices->total(),
                            ]) }}
                        </p>
                    @endif
                </div>
                <div class="flex flex-wrap shrink-0 items-center gap-2">
                    <a href="{{ route('invoices.export', request()->query()) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F4C81] shadow-sm transition hover:bg-blue-50 dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD] dark:hover:bg-[#1E3A5F]/50">{{ __('invoices.export_excel') }}</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.field_invoice_number') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.field_patient') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.field_amounts') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.field_status') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('invoices.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('invoices.show', $invoice) }}" data-no-spa class="group block min-w-0">
                                        <span class="flex min-w-0 items-center gap-2">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white dark:ring-[#1F2937]" style="background: {{ $invoice->statusColor() }}" aria-hidden="true"></span>
                                            <span class="font-semibold text-gray-900 transition group-hover:text-[#0F4C81] dark:text-[#F3F4F6] dark:group-hover:text-[#93C5FD]">{{ $invoice->invoice_number }}</span>
                                        </span>
                                        <span class="mt-0.5 block text-xs tabular-nums text-slate-500 dark:text-slate-400">@safeDate($invoice->created_at)</span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="block font-medium text-gray-900 dark:text-[#F3F4F6]">{{ optional($invoice->patient)->full_name ?? __('common.em_dash') }}</span>
                                    @if ($invoice->treatingDoctor)
                                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $invoice->treatingDoctor->full_name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 tabular-nums sm:px-5">
                                    <span class="block font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ number_format((float) $invoice->total, 2) }}</span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('invoices.paid_short') }} {{ number_format((float) $invoice->paid, 2) }}
                                        · {{ __('invoices.remaining_short') }} {{ number_format($invoice->balanceRemaining(), 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <x-invoice.status-badge :status="$invoice->status" />
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <a href="{{ route('invoices.show', $invoice) }}" data-no-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('invoices.action_view') }}</a>
                                        <a href="{{ route('invoices.edit', $invoice) }}" data-no-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('invoices.action_edit') }}</a>
                                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline" data-confirm-title="{{ __('invoices.confirm_delete_title') }}" data-confirm="{{ __('invoices.confirm_delete_body') }}">
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
                                    <p class="m-0 text-base font-semibold text-gray-700 dark:text-gray-200">{{ __('invoices.empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $hasFilters ? __('invoices.no_matching_results') : __('invoices.empty_hint') }}</p>
                                    @if (! $hasFilters)
                                        @can('create', \App\Models\Invoice::class)
                                            <a href="{{ route('invoices.create') }}" data-no-spa class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('invoices.add_new') }}</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="border-t border-gray-100 bg-[#FAFBFC] px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/40 sm:px-5">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
