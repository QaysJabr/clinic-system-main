@php
    $cur = $clinic->currency;
    $pmLabels = \App\Models\Expense::paymentMethodLabels();
    $paid = $expense->paidTotal();
    $remaining = $expense->remainingAmount();
    $statusLabel = $remaining <= 0.009 ? __('expenses.status_full_settled') : ($paid > 0.009 ? __('expenses.status_partial_settled') : __('expenses.status_unpaid'));
    $clinicTitle = $clinic->clinic_name ?: config('app.name', __('expenses.print_fallback_clinic'));
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
    $clinicLogoSrc = ($exportRender ?? false)
        ? \App\Support\ClinicDocumentLogo::src($clinic, true)
        : $clinic->logoPublicUrl();
@endphp

<div class="border-b border-gray-200 pb-6 mb-6">
    <div class="flex flex-wrap items-start gap-6">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-400 m-0">{{ __('expenses.document_tag') }}</p>
            <h1 class="mt-2 text-2xl font-bold text-[#0F4C81] m-0">{{ $clinicTitle }}</h1>
            @if($clinicContact !== '')
                <p class="mt-1 text-sm text-gray-600 m-0">{{ $clinicContact }}</p>
            @endif
        </div>
        @if($clinicLogoSrc)
            <div class="shrink-0">
                <img src="{{ $clinicLogoSrc }}" alt="" class="h-16 max-w-[180px] object-contain">
            </div>
        @endif
    </div>
</div>

<div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-3 text-base font-bold text-gray-900 m-0">{{ __('expenses.section_details') }}</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_document_number') }}</dt><dd class="font-semibold text-gray-900 m-0">{{ $expense->documentNumber() }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_expense_title_label') }}</dt><dd class="font-semibold text-gray-900 m-0">{{ $expense->title }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_category') }}</dt><dd class="font-medium text-gray-900 m-0">{{ optional($expense->category)->name ?? __('common.em_dash') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_settlement_type') }}</dt><dd class="font-medium text-gray-900 m-0">{{ $expense->isInstallments() ? __('expenses.settlement_installments_word') : __('expenses.settlement_single_word') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_commitment_date_print') }}</dt><dd class="font-medium text-gray-900 m-0">@safeDate($expense->expense_date)</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('common.status') }}</dt><dd class="font-semibold text-gray-900 m-0">{{ $statusLabel }}</dd></div>
        </dl>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-3 text-base font-bold text-gray-900 m-0">{{ __('expenses.section_totals_print') }}</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.section_summary_total') }}</dt><dd class="tabular-nums font-bold text-[#0F4C81] m-0">{{ number_format((float) $expense->amount, 2) }}@if(filled($cur)) {{ $cur }}@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.section_summary_paid') }}</dt><dd class="tabular-nums font-semibold text-emerald-700 m-0">{{ number_format($paid, 2) }}@if(filled($cur)) {{ $cur }}@endif</dd></div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-2"><dt class="text-gray-800 m-0 font-bold">{{ __('expenses.section_summary_remaining') }}</dt><dd class="tabular-nums font-bold text-amber-700 m-0">{{ number_format($remaining, 2) }}@if(filled($cur)) {{ $cur }}@endif</dd></div>
        </dl>
    </div>
</div>

<div class="mb-8">
    <h2 class="mb-3 text-base font-bold text-gray-900 m-0">{{ __('expenses.section_payments') }}</h2>
    <div class="overflow-hidden rounded-xl border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right font-bold text-gray-700">{{ __('expenses.field_amount_currency') }}</th>
                    <th class="px-4 py-3 text-right font-bold text-gray-700">{{ __('expenses.field_pay_date') }}</th>
                    <th class="px-4 py-3 text-right font-bold text-gray-700">{{ __('expenses.th_payment_method') }}</th>
                    <th class="px-4 py-3 text-right font-bold text-gray-700">{{ __('expenses.th_notes') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($expense->paymentsOrdered as $p)
                    <tr>
                        <td class="px-4 py-3 tabular-nums font-semibold text-gray-900">{{ number_format((float) $p->amount, 2) }}@if(filled($cur)) <span class="text-gray-500">{{ $cur }}</span>@endif</td>
                        <td class="px-4 py-3 text-gray-700">@safeDate($p->paid_at)</td>
                        <td class="px-4 py-3 text-gray-700">{{ $pmLabels[$p->payment_method] ?? $p->payment_method }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $p->notes ?? __('common.em_dash') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($expense->notes)
    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-2 text-base font-bold text-gray-900 m-0">{{ __('expenses.th_notes') }}</h2>
        <p class="text-sm leading-relaxed text-gray-700 m-0 whitespace-pre-wrap">{{ $expense->notes }}</p>
    </div>
@endif
