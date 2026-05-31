@php
    $clinic = \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $pmOpts = \App\Support\PaymentMethods::options();
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[900px]" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
        <div class="mb-6">
            <a href="{{ route('expenses.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('expenses.btn_back_index') }}</a>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('expenses.page_title_create') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('expenses.subtitle_create') }}</p>
        </div>

        <x-expense.nav active="list" />

        @if($categories->isEmpty())
            <div class="mb-6 overflow-hidden rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-4 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/25 sm:px-5" role="status">
                <p class="m-0 text-sm font-bold text-amber-900 dark:text-amber-200">{{ __('expenses.alert_no_active_categories_title') }}</p>
                <p class="mt-2 m-0 text-sm text-amber-900/90 dark:text-amber-100/90">{{ __('expenses.alert_no_active_categories_body') }}</p>
                @can('manage expense categories')
                    <a href="{{ route('expense-categories.create') }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">{{ __('expenses.btn_add_category') }}</a>
                @endcan
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
                <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                    <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('expenses.form_save_failed') }}</p>
                </div>
                <ul class="m-0 list-none space-y-1.5 px-4 py-3 text-sm text-red-800 sm:px-5 dark:text-red-200">
                    @foreach ($errors->all() as $error)
                        <li class="flex gap-2">
                            <span class="text-red-600 dark:text-red-400" aria-hidden="true">•</span>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="bg-[#0F4C81] px-4 py-3 text-white sm:px-5">
                <h2 class="text-base font-bold m-0">{{ __('expenses.form_section_expense_data') }}</h2>
            </div>
            <div class="p-5 sm:p-6">
                <form action="{{ route('expenses.store') }}" method="POST" class="space-y-6" novalidate id="expense-create-form">
                    @csrf

                    <div class="md:col-span-2 rounded-lg border border-slate-200 bg-slate-50/60 p-4 dark:border-[#374151] dark:bg-[#111827]/50">
                        <p class="mb-3 m-0 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.field_settlement_heading') }}</p>
                        <div class="flex flex-wrap gap-6">
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-800 dark:text-[#E5E7EB]">
                                <input type="radio" name="settlement_type" value="{{ \App\Models\Expense::SETTLEMENT_FULL }}" {{ old('settlement_type', \App\Models\Expense::SETTLEMENT_FULL) === \App\Models\Expense::SETTLEMENT_FULL ? 'checked' : '' }} id="st_full" onchange="window.__expStToggle && window.__expStToggle()">
                                <span>{{ __('expenses.settlement_full_description') }}</span>
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-800 dark:text-[#E5E7EB]">
                                <input type="radio" name="settlement_type" value="{{ \App\Models\Expense::SETTLEMENT_INSTALLMENTS }}" {{ old('settlement_type') === \App\Models\Expense::SETTLEMENT_INSTALLMENTS ? 'checked' : '' }} id="st_inst" onchange="window.__expStToggle && window.__expStToggle()">
                                <span>{{ __('expenses.settlement_installments_description') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="expense_category_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_category') }}</label>
                            <select name="expense_category_id" id="expense_category_id" required @disabled($categories->isEmpty())
                                class="@error('expense_category_id') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="">{{ __('expenses.placeholder_select_category') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('expense_category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="title" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.th_title') }}</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                class="@error('title') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('title')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_total_amount_hint') }}@if(filled($cur)) ({{ $cur }})@endif</label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0" required
                                class="@error('amount') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('amount')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expense_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_expense_date_edit') }}</label>
                            <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required
                                class="@error('expense_date') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('expense_date')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="wrap_payment_method_full" class="{{ old('settlement_type', \App\Models\Expense::SETTLEMENT_FULL) === \App\Models\Expense::SETTLEMENT_INSTALLMENTS ? 'hidden' : '' }}">
                            <label for="payment_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.th_payment_method') }}</label>
                            <select name="payment_method" id="payment_method"
                                class="@error('payment_method') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                @foreach($pmOpts as $code => $plabel)
                                    <option value="{{ $code }}" {{ old('payment_method', 'cash') === $code ? 'selected' : '' }}>{{ $plabel }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="installment_first_block" class="md:col-span-2 rounded-lg border border-amber-200/80 bg-amber-50/40 p-4 dark:border-amber-900/40 dark:bg-amber-950/20 {{ old('settlement_type') === \App\Models\Expense::SETTLEMENT_INSTALLMENTS ? '' : 'hidden' }}">
                            <p class="m-0 text-sm font-bold text-amber-900 dark:text-amber-200">{{ __('expenses.first_payment_heading') }}</p>
                            <p class="mt-1 m-0 text-xs text-amber-900/80 dark:text-amber-100/80">{{ __('expenses.first_payment_hint') }}</p>
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label for="first_payment_amount" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.first_payment_amount_label') }}</label>
                                    <input type="number" name="first_payment_amount" id="first_payment_amount" value="{{ old('first_payment_amount') }}" step="0.01" min="0.01" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                </div>
                                <div>
                                    <label for="first_payment_paid_at" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.field_pay_date') }}</label>
                                    <input type="date" name="first_payment_paid_at" id="first_payment_paid_at" value="{{ old('first_payment_paid_at', now()->format('Y-m-d')) }}" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                </div>
                                <div>
                                    <label for="first_payment_method" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.th_payment_method') }}</label>
                                    <select name="first_payment_method" id="first_payment_method" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                        @foreach($pmOpts as $code => $plabel)
                                            <option value="{{ $code }}" {{ old('first_payment_method', 'cash') === $code ? 'selected' : '' }}>{{ $plabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <label for="first_payment_notes" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.first_payment_notes_label') }}</label>
                                    <input type="text" name="first_payment_notes" id="first_payment_notes" value="{{ old('first_payment_notes') }}" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.notes_optional_short') }}</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="@error('notes') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-6 dark:border-[#374151]">
                        <a href="{{ route('expenses.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.back') }}</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] disabled:cursor-not-allowed disabled:opacity-60 dark:bg-[#3B82F6] dark:hover:bg-blue-600" @disabled($categories->isEmpty())>{{ __('common.save') }}</button>
                    </div>
                </form>
                <script @if(! empty($cspNonce ?? null)) nonce="{{ $cspNonce }}" @endif>
                    window.__expStToggle = function () {
                        var inst = document.getElementById('st_inst') && document.getElementById('st_inst').checked;
                        var wrap = document.getElementById('wrap_payment_method_full');
                        var block = document.getElementById('installment_first_block');
                        var sel = document.getElementById('payment_method');
                        if (wrap) wrap.classList.toggle('hidden', !!inst);
                        if (block) block.classList.toggle('hidden', !inst);
                        if (sel) { sel.disabled = !!inst; if (inst) sel.removeAttribute('required'); else sel.setAttribute('required', 'required'); }
                    };
                    document.addEventListener('DOMContentLoaded', window.__expStToggle);
                </script>
            </div>
        </div>
    </div>
