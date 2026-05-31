@php
    $clinic = $clinic ?? \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $pmLabels = \App\Models\Expense::paymentMethodLabels();
    $paid = $expense->paidTotal();
    $remaining = $expense->remainingAmount();
    $status = $expense->settlementStatusFromPaid($paid);
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1100px]" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('expenses.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('expenses.btn_back_index') }}</a>
        <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('expenses.document_tag') }}</p>
            <h1 class="mt-1 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ $expense->title }}</h1>
            <p class="mt-2 m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ $expense->documentNumber() }} · {{ optional($expense->category)->name ?? __('common.em_dash') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-expense.status-badge :status="$status" />
            <a href="{{ route('expenses.print', $expense) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('expenses.btn_print') }}</a>
            <a href="{{ route('expenses.pdf', $expense) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('expenses.btn_pdf') }}</a>
            <a href="{{ route('expenses.edit', $expense) }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('expenses.action_edit') }}</a>
        </div>
        </div>
    </div>

    <x-expense.nav :expense="$expense" active="show" />

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('expenses.section_summary_total') }}</p>
            <p class="mt-2 m-0 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format((float) $expense->amount, 2) }}@if(filled($cur)) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif</p>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('expenses.section_summary_paid') }}</p>
            <p class="mt-2 m-0 text-2xl font-bold tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($paid, 2) }}@if(filled($cur)) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif</p>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-[#94A3B8]">{{ __('expenses.section_summary_remaining') }}</p>
            <p class="mt-2 m-0 text-2xl font-bold tabular-nums text-amber-700 dark:text-amber-300">{{ number_format($remaining, 2) }}@if(filled($cur)) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif</p>
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.section_details') }}</h2>
        </div>
        <div class="p-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500 dark:text-[#9CA3AF]">{{ __('expenses.field_settlement_type') }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $expense->isInstallments() ? __('expenses.settlement_installments') : __('expenses.settlement_full') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-[#9CA3AF]">{{ __('expenses.field_expense_commitment_date') }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $expense->expense_date?->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-[#9CA3AF]">{{ __('expenses.field_payment_method_header') }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $pmLabels[$expense->payment_method] ?? $expense->payment_method }}</dd></div>
                <div><dt class="text-gray-500 dark:text-[#9CA3AF]">{{ __('expenses.field_created_by') }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ optional($expense->creator)->name ?? __('common.em_dash') }}</dd></div>
                @if($expense->notes)
                    <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-[#9CA3AF]">{{ __('expenses.th_notes') }}</dt><dd class="mt-1 whitespace-pre-wrap text-gray-800 dark:text-[#E5E7EB]">{{ $expense->notes }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#0F4C81] px-4 py-3 text-white sm:px-5">
            <h2 class="m-0 text-base font-bold">{{ __('expenses.section_payments') }}</h2>
            @if($expense->isInstallments() && $remaining > 0.009)
                <span class="text-xs font-medium text-white/90">{{ __('expenses.hint_can_add_installments') }}</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.payment_th_serial') }}</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.field_amount_currency') }}</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.field_pay_date') }}</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.payment_th_method') }}</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.th_notes') }}</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('expenses.payment_th_receipt') }}</th>
                        @if($expense->isInstallments())
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('expenses.payment_th_delete_action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($expense->paymentsOrdered as $p)
                        <tr class="border-b border-gray-100 dark:border-[#374151]">
                            <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $p->id }}</td>
                            <td class="px-4 py-3 font-semibold tabular-nums text-gray-900 dark:text-[#F3F4F6] sm:px-5">{{ number_format((float) $p->amount, 2) }}@if(filled($cur)) <span class="text-xs text-gray-400">{{ $cur }}</span>@endif</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $p->paid_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $pmLabels[$p->payment_method] ?? $p->payment_method }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $p->notes ? \Illuminate\Support\Str::limit($p->notes, 40) : __('common.em_dash') }}</td>
                            <td class="px-4 py-3 sm:px-5">
                                <a href="{{ route('expenses.payment-print', [$expense, $p]) }}" target="_blank" rel="noopener" class="font-semibold text-[#1F7A8C] hover:underline">{{ __('expenses.btn_print') }}</a>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <a href="{{ route('expenses.payment-pdf', [$expense, $p]) }}" class="font-semibold text-[#1F7A8C] hover:underline">{{ __('expenses.btn_pdf') }}</a>
                            </td>
                            @if($expense->isInstallments())
                                <td class="px-4 py-3 sm:px-5">
                                    <form method="POST" action="{{ route('expenses.payments.destroy', [$expense, $p]) }}" data-confirm-title="{{ __('expenses.payment_confirm_delete_title') }}" data-confirm="{{ __('expenses.payment_confirm_delete_body') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-600 hover:underline">{{ __('expenses.confirm_delete_payment_submit') }}</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $expense->isInstallments() ? 7 : 6 }}" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF]">{{ __('expenses.no_payments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($expense->isInstallments() && $remaining > 0.009)
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.form_add_payment') }}</h2>
            </div>
            <div class="p-5 sm:p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
                        <ul class="m-0 list-none space-y-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('expenses.payments.store', $expense) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label for="pay_amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_amount_currency') }}@if(filled($cur)) ({{ $cur }})@endif</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $remaining }}" name="amount" id="pay_amount" value="{{ old('amount') }}" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div>
                        <label for="paid_at" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_pay_date') }}</label>
                        <input type="date" name="paid_at" id="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div>
                        <label for="pay_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.th_payment_method') }}</label>
                        <select name="payment_method" id="pay_method" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            @foreach($pmLabels as $val => $label)
                                <option value="{{ $val }}" {{ old('payment_method', 'cash') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="pay_notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.notes_optional_short') }}</label>
                        <input type="text" name="notes" id="pay_notes" value="{{ old('notes') }}" maxlength="5000" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('expenses.submit_record_payment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($expense->isFullSettlement())
        <p class="mt-4 text-center text-sm text-gray-500 dark:text-[#9CA3AF]">{!! __('expenses.full_settlement_note_html', ['url' => route('expenses.edit', $expense)]) !!}</p>
    @endif
</div>
