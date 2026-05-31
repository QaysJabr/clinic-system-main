@php
    $oldType = old('compensation_type', 'fixed');
    $oldCycle = old('payment_cycle', 'monthly');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true); @endphp
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}" x-data="{ type: '{{ $oldType }}', cycle: '{{ $oldCycle }}' }">
        <div class="mb-6">
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('staff.comp_profile_new_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('staff.comp_profile_single_constraint') }}</p>
        </div>

        <x-staff.nav active="compensation" />

        @if($staffList->isEmpty())
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/25 dark:text-amber-200 sm:px-5">
                {{ __('staff.comp_profile_all_assigned') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
                <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                    <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('staff.comp_profile_save_error_header') }}</p>
                </div>
                <ul class="m-0 list-none space-y-1.5 px-4 py-3 text-sm text-red-800 sm:px-5 dark:text-red-200">
                    @foreach ($errors->all() as $error)
                        <li class="flex gap-2"><span class="text-red-600 dark:text-red-400">•</span><span>{{ $error }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dash-section-group m-0">
            <p class="dash-section-label m-0">{{ __('staff.comp_profile_card_title') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="p-5 sm:p-6">
                <form action="{{ route('staff-compensation-profiles.store') }}" method="POST" class="space-y-8" novalidate @if($staffList->isEmpty()) aria-disabled="true" @endif>
                    @csrf

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="staff_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_staff') }}</label>
                            <select name="staff_id" id="staff_id" required @disabled($staffList->isEmpty())
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="">{{ __('staff.comp_profile_pick_staff') }}</option>
                                @foreach($staffList as $s)
                                    <option value="{{ $s->id }}" {{ (string) old('staff_id', request('staff_id')) === (string) $s->id ? 'selected' : '' }}>{{ $s->full_name }} — {{ $s->roleTypeLabel() }}@if(filled($s->email)) ({{ $s->email }})@endif</option>
                                @endforeach
                            </select>
                            @error('staff_id')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <p class="m-0 mb-3 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.comp_profile_model_title') }}</p>
                            <label for="compensation_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_model_pick') }}</label>
                            <select name="compensation_type" id="compensation_type" x-model="type" required
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="fixed">{{ __('staff.comp_fixed') }}</option>
                                <option value="percentage">{{ __('staff.comp_percentage') }}</option>
                                <option value="daily">{{ __('staff.comp_daily') }}</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-[#9CA3AF]" x-show="type === 'fixed'" x-cloak>{{ __('staff.comp_profile_model_fixed_hint') }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-[#9CA3AF]" x-show="type === 'percentage'" x-cloak>{{ __('staff.comp_profile_model_percentage_hint') }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-[#9CA3AF]" x-show="type === 'daily'" x-cloak>{{ __('staff.comp_profile_model_daily_hint') }}</p>
                            @error('compensation_type')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <p class="m-0 mb-3 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.comp_profile_cycle_title') }}</p>
                            <label for="payment_cycle" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_cycle_pick') }}</label>
                            <select name="payment_cycle" id="payment_cycle" x-model="cycle" required
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="weekly">{{ __('payroll.cycle_weekly') }}</option>
                                <option value="monthly">{{ __('payroll.cycle_monthly') }}</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-[#9CA3AF]" x-show="cycle === 'weekly'" x-cloak>{{ __('staff.comp_profile_cycle_weekly_hint') }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-[#9CA3AF]" x-show="cycle === 'monthly'" x-cloak>{{ __('staff.comp_profile_cycle_monthly_hint') }}</p>
                            @error('payment_cycle')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="start_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_start_date') }}</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            @error('start_date')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_status') }}</label>
                            <select name="status" id="status" required
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('staff.status_active') }}</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('staff.status_inactive') }}</option>
                            </select>
                            @error('status')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <p class="m-0 mb-3 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.comp_profile_details_title') }}</p>

                        <div class="space-y-4 rounded-lg border border-slate-100 bg-slate-50/50 p-4 dark:border-[#374151] dark:bg-[#111827]/50" x-show="type === 'fixed'" x-cloak>
                            <div>
                                <label for="base_salary" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_base_salary') }}</label>
                                <input type="number" step="0.01" min="0" name="base_salary" id="base_salary" value="{{ old('base_salary') }}"
                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                @error('base_salary')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="space-y-4 rounded-lg border border-slate-100 bg-slate-50/50 p-4 dark:border-[#374151] dark:bg-[#111827]/50" x-show="type === 'percentage'" x-cloak>
                            <div>
                                <label for="percentage_rate" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_percentage') }}</label>
                                <input type="number" step="0.01" min="0" max="100" name="percentage_rate" id="percentage_rate" value="{{ old('percentage_rate') }}"
                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                @error('percentage_rate')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="calculation_basis" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_basis') }}</label>
                                <select name="calculation_basis" id="calculation_basis"
                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                    <option value="invoice_paid_total" {{ old('calculation_basis', 'invoice_paid_total') === 'invoice_paid_total' ? 'selected' : '' }}>{{ __('staff.basis_invoice_paid_total') }}</option>
                                    <option value="gross_revenue" {{ old('calculation_basis') === 'gross_revenue' ? 'selected' : '' }}>{{ __('staff.basis_gross_revenue') }}</option>
                                </select>
                                @error('calculation_basis')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="space-y-4 rounded-lg border border-slate-100 bg-slate-50/50 p-4 dark:border-[#374151] dark:bg-[#111827]/50" x-show="type === 'daily'" x-cloak>
                            <div>
                                <label for="daily_wage" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_daily_wage') }}</label>
                                <input type="number" step="0.01" min="0" name="daily_wage" id="daily_wage" value="{{ old('daily_wage') }}"
                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                                @error('daily_wage')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('staff.comp_profile_notes') }}</label>
                        <textarea name="notes" id="notes" rows="2" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 bg-gray-50/50 pt-6 dark:border-[#374151] dark:bg-[#111827]/50">
                        <a href="{{ route('staff-compensation-profiles.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('staff.btn_back') }}</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600" @disabled($staffList->isEmpty())>{{ __('staff.btn_save') }}</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
