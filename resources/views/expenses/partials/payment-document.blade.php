@php
    $cur = $clinic->currency;
    $clinicTitle = $clinic->clinic_name ?: config('app.name', __('expenses.print_fallback_clinic'));
    $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' · ');
@endphp

<div class="border-b border-gray-200 pb-6 mb-6">
    <p class="text-xs font-bold uppercase tracking-wide text-gray-400 m-0">{{ __('expenses.pdf_html_title_payment') }}</p>
    <h1 class="mt-2 text-2xl font-bold text-[#0F4C81] m-0">{{ $clinicTitle }}</h1>
    @if($clinicContact !== '')
        <p class="mt-1 text-sm text-gray-600 m-0">{{ $clinicContact }}</p>
    @endif
</div>

<div class="mb-8 rounded-xl border border-gray-200 bg-white p-5">
    <h2 class="mb-3 text-base font-bold text-gray-900 m-0">{{ __('expenses.payment_print_related_expense_heading') }}</h2>
    <dl class="space-y-2 text-sm">
        <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_expense_document') }}</dt><dd class="font-semibold m-0">{{ $expense->documentNumber() }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_expense_title_label') }}</dt><dd class="font-semibold m-0">{{ $expense->title }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('expenses.field_category') }}</dt><dd class="m-0">{{ optional($expense->category)->name ?? __('common.em_dash') }}</dd></div>
    </dl>
</div>

<div class="rounded-xl border border-[#0F4C81]/20 bg-[#F7F9FC] p-6">
    <h2 class="mb-4 text-base font-bold text-[#0F4C81] m-0">{{ __('expenses.payment_print_payment_details_heading') }}</h2>
    <dl class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><dt class="text-gray-600 m-0">{{ __('expenses.pdf_section_amount_paid') }}</dt><dd class="text-xl font-bold tabular-nums text-[#0F4C81] m-0">{{ number_format((float) $payment->amount, 2) }}@if(filled($cur)) <span class="text-base text-gray-600">{{ $cur }}</span>@endif</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-600 m-0">{{ __('expenses.field_pay_date') }}</dt><dd class="font-semibold m-0">@safeDate($payment->paid_at)</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-600 m-0">{{ __('expenses.th_payment_method') }}</dt><dd class="font-semibold m-0">{{ \App\Support\PaymentMethods::label($payment->payment_method) }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-600 m-0">{{ __('expenses.field_recorded_by') }}</dt><dd class="m-0">{{ optional($payment->creator)->name ?? __('common.em_dash') }}</dd></div>
        @if($payment->notes)
            <div class="border-t border-gray-200 pt-3"><dt class="text-gray-600 m-0 mb-1">{{ __('expenses.th_notes') }}</dt><dd class="m-0 whitespace-pre-wrap text-gray-800">{{ $payment->notes }}</dd></div>
        @endif
    </dl>
</div>
