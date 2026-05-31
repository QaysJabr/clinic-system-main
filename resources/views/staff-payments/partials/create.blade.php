@php
    $clinic = \App\Support\ClinicSettings::current();
    $cur = $clinic->currency;
    $defaultPeriodStart = now()->startOfMonth()->format('Y-m-d');
    $defaultPeriodEnd = now()->endOfMonth()->format('Y-m-d');
    $pmOpts = \App\Support\PaymentMethods::options();
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
        <div class="mb-6">
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('payroll.page_title_create') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('payroll.subtitle_create') }}</p>
        </div>

        <x-staff.nav active="payments" />

        @if($staffList->isEmpty())
            <div class="mb-6 overflow-hidden rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-4 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/25 sm:px-5" role="status">
                <p class="m-0 text-sm font-bold text-amber-900 dark:text-amber-200">{{ __('payroll.alert_no_staff_title') }}</p>
                <p class="mt-2 m-0 text-sm text-amber-900/90 dark:text-amber-100/90">{{ __('payroll.alert_no_staff_body') }}</p>
                @can('manage staff')
                    <a href="{{ route('staff.create') }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">{{ __('payroll.btn_add_staff') }}</a>
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
                <h2 class="text-base font-bold m-0">{{ __('payroll.form_section_payment_data') }}</h2>
            </div>
            <div class="p-5 sm:p-6">
                <form action="{{ route('staff-payments.store') }}" method="POST" class="space-y-6" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="staff_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.th_staff') }}</label>
                            <select name="staff_id" id="staff_id" required @disabled($staffList->isEmpty())
                                class="@error('staff_id') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="">{{ __('payroll.placeholder_select_staff') }}</option>
                                @foreach($staffList as $s)
                                    <option value="{{ $s->id }}" {{ (string) old('staff_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                                @endforeach
                            </select>
                            @error('staff_id')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="period_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.filter_period_type') }}</label>
                            <select name="period_type" id="period_type" required
                                class="@error('period_type') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                @foreach([\App\Enums\PaymentCycle::Monthly, \App\Enums\PaymentCycle::Weekly] as $cycle)
                                    <option value="{{ $cycle->value }}" {{ old('period_type', 'monthly') === $cycle->value ? 'selected' : '' }}>{{ $cycle->label() }}</option>
                                @endforeach
                            </select>
                            @error('period_type')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="period_start" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_period_start_short') }}</label>
                            <input type="date" name="period_start" id="period_start" value="{{ old('period_start', $defaultPeriodStart) }}" required
                                class="@error('period_start') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('period_start')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="period_end" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_period_end_short') }}</label>
                            <input type="date" name="period_end" id="period_end" value="{{ old('period_end', $defaultPeriodEnd) }}" required
                                class="@error('period_end') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('period_end')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="compensation_profile_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_compensation_profile_optional') }}</label>
                            <select name="compensation_profile_id" id="compensation_profile_id"
                                class="@error('compensation_profile_id') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="">{{ __('payroll.placeholder_none') }}</option>
                                @foreach($compensationProfiles as $cp)
                                    <option value="{{ $cp->id }}" {{ (string) old('compensation_profile_id') === (string) $cp->id ? 'selected' : '' }}>{{ optional($cp->staff)->full_name ?? __('common.em_dash') }} — #{{ $cp->id }}</option>
                                @endforeach
                            </select>
                            @error('compensation_profile_id')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="days_worked" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_days_worked_optional') }}</label>
                            <input type="number" name="days_worked" id="days_worked" value="{{ old('days_worked') }}" min="0" max="400"
                                class="@error('days_worked') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('days_worked')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="base_amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_base_amount') }}@if(filled($cur)) ({{ $cur }})@endif</label>
                            <input type="number" name="base_amount" id="base_amount" value="{{ old('base_amount') }}" step="0.01" min="0" required
                                class="@error('base_amount') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('base_amount')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bonus" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_bonus_optional') }}</label>
                            <input type="number" name="bonus" id="bonus" value="{{ old('bonus', '0') }}" step="0.01" min="0"
                                class="@error('bonus') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('bonus')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="deduction" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_deduction_optional') }}</label>
                            <input type="number" name="deduction" id="deduction" value="{{ old('deduction', '0') }}" step="0.01" min="0"
                                class="@error('deduction') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('deduction')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="paid_amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_paid_required') }}@if(filled($cur)) ({{ $cur }})@endif <span class="text-red-600">*</span></label>
                            <input type="number" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', '0') }}" step="0.01" min="0" required
                                class="@error('paid_amount') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('paid_amount')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @include('staff-payments._payroll_preview')

                        <div>
                            <label for="payment_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_payment_date_optional') }}</label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date') }}"
                                class="@error('payment_date') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('payment_date')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_payment_method_optional') }}</label>
                            <select name="payment_method" id="payment_method"
                                class="@error('payment_method') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="">{{ __('payroll.placeholder_none') }}</option>
                                @foreach($pmOpts as $code => $label)
                                    <option value="{{ $code }}" {{ old('payment_method') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.field_notes_optional') }}</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="@error('notes') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-6 dark:border-[#374151]">
                        <a href="{{ route('staff-payments.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('payroll.btn_back_to_list') }}</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] disabled:cursor-not-allowed disabled:opacity-60 dark:bg-[#3B82F6] dark:hover:bg-blue-600" @disabled($staffList->isEmpty())>{{ __('payroll.btn_save_payroll_record') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
