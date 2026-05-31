@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $remaining = $invoice->balanceRemaining();
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
@endphp
<div class="mx-auto w-full max-w-[1100px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('invoices.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('invoices.back_to_index') }}</a>
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                @if ($invoice->patient)
                    <x-patient.avatar :name="$invoice->patient->full_name" size="lg" />
                @endif
                <div class="min-w-0">
                    <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ $invoice->invoice_number }}</h1>
                    <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ optional($invoice->patient)->full_name ?? __('common.em_dash') }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <x-invoice.status-badge :status="$invoice->status" />
                        <span class="text-xs tabular-nums text-slate-500 dark:text-slate-400">@safeDate($invoice->created_at)</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F4C81] shadow-sm hover:bg-blue-50 dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD]">{{ __('invoices.print') }}</a>
                <a href="{{ route('invoices.pdf', $invoice) }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6]">{{ __('invoices.export_pdf') }}</a>
                @can('update', $invoice)
                    <a href="{{ route('invoices.edit', $invoice) }}" data-no-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('invoices.action_edit') }}</a>
                @endcan
            </div>
        </div>
    </div>

    <x-invoice.nav :invoice="$invoice" active="show" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('invoices.field_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-gray-900 dark:text-[#F3F4F6]">{{ number_format((float) $invoice->total, 2) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('invoices.field_paid') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-800 dark:text-emerald-300">{{ number_format((float) $invoice->paid, 2) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('invoices.field_remaining') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($remaining, 2) }}</p>
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('invoices.section_invoice_info') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('invoices.label_patient') }}</p>
                <p class="m-0 mt-1 text-sm font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ optional($invoice->patient)->full_name ?? __('common.em_dash') }}</p>
            </div>
            <div>
                <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('invoices.field_doctor') }}</p>
                <p class="m-0 mt-1 text-sm text-gray-800 dark:text-[#F3F4F6]">{{ optional($invoice->treatingDoctor)->full_name ?? __('common.em_dash') }}</p>
            </div>
            @if ($invoice->visit)
                <div>
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('invoices.label_visit') }}</p>
                    <p class="m-0 mt-1 text-sm text-gray-800 dark:text-[#F3F4F6]">{{ $invoice->visit->chief_complaint ?: __('common.em_dash') }}</p>
                </div>
            @endif
            @if ($invoice->due_date)
                <div>
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('invoices.field_due_date_column') }}</p>
                    <p class="m-0 mt-1 text-sm tabular-nums text-gray-800 dark:text-[#F3F4F6]">@safeDate($invoice->due_date)</p>
                </div>
            @endif
            @if ($invoice->notes)
                <div class="sm:col-span-2">
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('invoices.label_notes') }}</p>
                    <p class="m-0 mt-1 whitespace-pre-wrap text-sm text-gray-700 dark:text-[#E5E7EB]">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('invoices.section_items') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.th_item_name') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.label_price') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.label_quantity') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.th_line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr class="border-b border-gray-100 dark:border-[#374151]">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6] sm:px-5">{{ $item->item_name }}</td>
                            <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ number_format((float) $item->price, 2) }}</td>
                            <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 tabular-nums font-semibold text-gray-900 dark:text-[#F3F4F6] sm:px-5">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('invoices.no_line_items') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('invoices.section_payments') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('common.amount') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.payment_field_method') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.payment_field_date') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('invoices.field_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->payments as $payment)
                        <tr class="border-b border-gray-100 dark:border-[#374151]">
                            <td class="px-4 py-3 tabular-nums font-semibold text-emerald-800 dark:text-emerald-300 sm:px-5">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ \App\Support\PaymentMethods::label((string) $payment->payment_method) }}</td>
                            <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">@safeDate($payment->payment_date)</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $payment->notes ?? __('common.em_dash') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('invoices.no_payments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('manage payments')
        @if ($invoice->status !== 'paid')
            <div class="overflow-hidden rounded-xl border border-emerald-200/80 bg-emerald-50/30 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <div class="border-b border-emerald-100 px-4 py-4 dark:border-emerald-900/40 sm:px-5">
                    <h2 class="m-0 text-base font-bold text-emerald-900 dark:text-emerald-200">{{ __('invoices.add_payment_heading') }}</h2>
                    <p class="m-0 mt-1 text-xs text-emerald-800/80 dark:text-emerald-300/80">{{ __('invoices.add_payment_hint', ['amount' => number_format($remaining, 2)]) }}</p>
                </div>
                <form method="POST" action="{{ route('payments.store') }}" class="p-4 sm:p-5">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="amount" class="{{ $labelClass }}">{{ __('common.amount') }}</label>
                            <input type="number" step="0.01" id="amount" name="amount" max="{{ $remaining }}" value="{{ old('amount', $remaining > 0 ? $remaining : '') }}" class="{{ $inputClass }}" required>
                            @error('amount')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="payment_method" class="{{ $labelClass }}">{{ __('invoices.payment_field_method') }}</label>
                            <select id="payment_method" name="payment_method" class="{{ $inputClass }}" required>
                                @foreach (\App\Support\PaymentMethods::options() as $pmValue => $pmLabel)
                                    <option value="{{ $pmValue }}" @selected(old('payment_method', 'cash') === $pmValue)>{{ $pmLabel }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="payment_date" class="{{ $labelClass }}">{{ __('invoices.payment_field_date') }}</label>
                            <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="{{ $inputClass }}" required>
                            @error('payment_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="payment_notes" class="{{ $labelClass }}">{{ __('invoices.field_notes') }}</label>
                            <textarea id="payment_notes" name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 dark:bg-emerald-600">{{ __('invoices.btn_add_payment') }}</button>
                    </div>
                </form>
            </div>
        @endif
    @endcan
</div>
