@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
    $rulesActive = (int) (bool) $settings->require_invoice_for_visit + (int) (bool) $settings->enforce_one_invoice_per_visit;
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('settings.page_heading') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('settings.hero_subtitle') }}</p>
        </div>
    </div>

    <x-settings.nav active="clinic" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937] sm:col-span-2 lg:col-span-1">
            <div class="flex items-center gap-3">
                @if ($settings->logoPublicUrl())
                    <img src="{{ $settings->logoPublicUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-lg border border-slate-200 object-contain p-1 dark:border-[#374151]">
                @else
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500 dark:bg-[#111827] dark:text-slate-400">{{ __('settings.stat_no_logo') }}</div>
                @endif
                <div class="min-w-0">
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('settings.stat_clinic') }}</p>
                    <p class="m-0 mt-0.5 truncate text-base font-bold text-slate-900 dark:text-slate-100">{{ $settings->clinic_name ?: __('common.em_dash') }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-teal-200/90 bg-teal-50/50 p-4 shadow-sm dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-teal-900 dark:text-teal-300">{{ __('settings.stat_currency') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold text-teal-950 dark:text-teal-100">{{ $settings->currency ?: __('common.em_dash') }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200/90 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-indigo-900 dark:text-indigo-300">{{ __('settings.stat_opening_cash') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-indigo-950 dark:text-indigo-100">
                {{ number_format((float) ($settings->opening_cash_balance ?? 0), 2) }}
                @if(filled($settings->currency)) <span class="text-sm font-semibold text-slate-500">{{ $settings->currency }}</span>@endif
            </p>
        </div>
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-900 dark:text-violet-300">{{ __('settings.stat_rules_active') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-950 dark:text-violet-100">{{ $rulesActive }}<span class="text-sm font-semibold text-slate-500"> / 2</span></p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
            <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('settings.error_save_failed_title') }}</p>
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

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
        @csrf
        @method('PUT')

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('settings.section_identity') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="clinic_name" class="{{ $labelClass }}">{{ __('settings.label_clinic_name') }}</label>
                        <input type="text" name="clinic_name" id="clinic_name" value="{{ old('clinic_name', $settings->clinic_name) }}" required
                            class="{{ $inputClass }} @error('clinic_name') border-red-300 ring-red-200 @enderror">
                        @error('clinic_name')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="clinic_logo" class="{{ $labelClass }}">{{ __('settings.label_clinic_logo') }}</label>
                        @if ($settings->logoPublicUrl())
                            <div class="mb-3 flex flex-wrap items-center gap-4 rounded-lg border border-slate-200 bg-slate-50/80 p-3 dark:border-[#374151] dark:bg-[#111827]/60">
                                <img src="{{ $settings->logoPublicUrl() }}" alt="{{ __('settings.logo_alt') }}" class="h-16 max-w-[200px] object-contain">
                                <p class="m-0 text-xs text-gray-600 dark:text-[#9CA3AF]">{{ __('settings.logo_current_hint') }}</p>
                            </div>
                        @endif
                        <input type="file" name="clinic_logo" id="clinic_logo" accept="image/jpeg,image/png,image/jpg"
                            class="{{ $inputClass }} border-dashed file:me-4 file:rounded-md file:border-0 file:bg-[#0F4C81] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0c3d66] @error('clinic_logo') border-red-300 @enderror">
                        <p class="m-0 mt-1.5 text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.logo_file_hint') }}</p>
                        @error('clinic_logo')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('settings.section_contact') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                    <div>
                        <label for="clinic_phone" class="{{ $labelClass }}">{{ __('settings.label_phone') }}</label>
                        <input type="text" name="clinic_phone" id="clinic_phone" value="{{ old('clinic_phone', $settings->clinic_phone) }}" class="{{ $inputClass }} @error('clinic_phone') border-red-300 @enderror">
                        @error('clinic_phone')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="clinic_email" class="{{ $labelClass }}">{{ __('settings.label_email') }}</label>
                        <input type="email" name="clinic_email" id="clinic_email" value="{{ old('clinic_email', $settings->clinic_email) }}" class="{{ $inputClass }} @error('clinic_email') border-red-300 @enderror">
                        @error('clinic_email')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="clinic_address" class="{{ $labelClass }}">{{ __('settings.label_address') }}</label>
                        <textarea name="clinic_address" id="clinic_address" rows="3" class="{{ $inputClass }} @error('clinic_address') border-red-300 @enderror">{{ old('clinic_address', $settings->clinic_address) }}</textarea>
                        @error('clinic_address')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('settings.section_finance') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                    <div>
                        <label for="currency" class="{{ $labelClass }}">{{ __('settings.label_currency') }}</label>
                        <input type="text" name="currency" id="currency" value="{{ old('currency', $settings->currency) }}" placeholder="{{ __('settings.currency_placeholder') }}" class="{{ $inputClass }} @error('currency') border-red-300 @enderror">
                        @error('currency')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="opening_cash_balance" class="{{ $labelClass }}">{{ __('settings.label_opening_cash') }}</label>
                        <input type="number" step="0.01" name="opening_cash_balance" id="opening_cash_balance" value="{{ old('opening_cash_balance', $settings->opening_cash_balance ?? 0) }}" class="{{ $inputClass }} @error('opening_cash_balance') border-red-300 @enderror">
                        <p class="m-0 mt-1.5 text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.opening_cash_help') }}</p>
                        @error('opening_cash_balance')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('settings.section_documents') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="grid grid-cols-1 gap-6 p-5 sm:p-6">
                    <div>
                        <label for="invoice_notes" class="{{ $labelClass }}">{{ __('settings.label_invoice_notes') }}</label>
                        <textarea name="invoice_notes" id="invoice_notes" rows="4" class="{{ $inputClass }} @error('invoice_notes') border-red-300 @enderror">{{ old('invoice_notes', $settings->invoice_notes) }}</textarea>
                        @error('invoice_notes')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="report_footer" class="{{ $labelClass }}">{{ __('settings.label_report_footer') }}</label>
                        <textarea name="report_footer" id="report_footer" rows="4" class="{{ $inputClass }} @error('report_footer') border-red-300 @enderror">{{ old('report_footer', $settings->report_footer) }}</textarea>
                        @error('report_footer')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('settings.section_scheduling') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                    <div>
                        <label for="scheduling_slot_minutes" class="{{ $labelClass }}">{{ __('settings.label_slot_minutes') }}</label>
                        <input type="number" min="5" max="120" name="scheduling_slot_minutes" id="scheduling_slot_minutes"
                            value="{{ old('scheduling_slot_minutes', $settings->scheduling_slot_minutes ?? 15) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="scheduling_buffer_minutes" class="{{ $labelClass }}">{{ __('settings.label_buffer_minutes') }}</label>
                        <input type="number" min="0" max="60" name="scheduling_buffer_minutes" id="scheduling_buffer_minutes"
                            value="{{ old('scheduling_buffer_minutes', $settings->scheduling_buffer_minutes ?? 0) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="scheduling_day_start" class="{{ $labelClass }}">{{ __('settings.label_day_start') }}</label>
                        <input type="time" name="scheduling_day_start" id="scheduling_day_start"
                            value="{{ old('scheduling_day_start', \App\Support\Scheduling\SchedulingSettings::normalizeTime($settings->scheduling_day_start ?? '09:00')) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="scheduling_day_end" class="{{ $labelClass }}">{{ __('settings.label_day_end') }}</label>
                        <input type="time" name="scheduling_day_end" id="scheduling_day_end"
                            value="{{ old('scheduling_day_end', \App\Support\Scheduling\SchedulingSettings::normalizeTime($settings->scheduling_day_end ?? '17:00')) }}" class="{{ $inputClass }}">
                    </div>
                    <div class="md:col-span-2 space-y-4">
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 p-3 dark:border-[#374151]">
                            <input type="checkbox" name="scheduling_reminders_enabled" value="1" class="mt-1 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]"
                                @checked(old('scheduling_reminders_enabled', $settings->scheduling_reminders_enabled ?? true))>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('settings.checkbox_reminders_label') }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.checkbox_reminders_help') }}</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 p-3 dark:border-[#374151]">
                            <input type="checkbox" name="scheduling_allow_overbooking" value="1" class="mt-1 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]"
                                @checked(old('scheduling_allow_overbooking', $settings->scheduling_allow_overbooking ?? false))>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('settings.checkbox_overbooking_label') }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.checkbox_overbooking_help') }}</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('settings.section_visit_invoice_rules') }}</p>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                <div class="space-y-4 p-5 sm:p-6">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 p-3 transition hover:bg-slate-50/80 dark:border-[#374151] dark:hover:bg-[#111827]/50">
                        <input type="checkbox" name="require_invoice_for_visit" value="1" class="mt-1 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81] dark:border-[#4B5563]"
                            @checked(old('require_invoice_for_visit', $settings->require_invoice_for_visit))>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('settings.checkbox_require_invoice_visit_label') }}</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.checkbox_require_invoice_visit_help') }}</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 p-3 transition hover:bg-slate-50/80 dark:border-[#374151] dark:hover:bg-[#111827]/50">
                        <input type="checkbox" name="enforce_one_invoice_per_visit" value="1" class="mt-1 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81] dark:border-[#4B5563]"
                            @checked(old('enforce_one_invoice_per_visit', $settings->enforce_one_invoice_per_visit))>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ __('settings.checkbox_one_invoice_visit_label') }}</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.checkbox_one_invoice_visit_help') }}</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('settings.btn_save') }}</button>
        </div>
    </form>

    <div class="dash-section-group mt-6">
        <p class="dash-section-label m-0">{{ __('settings.section_public_booking') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#374151] dark:bg-[#1F2937] sm:p-6">
            <p class="m-0 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('settings.public_booking_intro') }}</p>
            @if (session('public_booking_url'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/25">
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('settings.public_booking_link_label') }}</p>
                    <input type="text" readonly value="{{ session('public_booking_url') }}" class="mt-2 block w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-mono text-slate-800 dark:border-emerald-900/50 dark:bg-[#111827] dark:text-slate-100" onclick="this.select()">
                    <p class="m-0 mt-2 text-xs text-emerald-900/80 dark:text-emerald-200/80">{{ __('settings.public_booking_link_hint') }}</p>
                </div>
            @endif
            <form action="{{ route('settings.public-booking-link') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-[#0F4C81] bg-[#0F4C81]/5 px-5 py-2.5 text-sm font-bold text-[#0F4C81] transition hover:bg-[#0F4C81]/10 dark:border-[#3B82F6] dark:bg-[#3B82F6]/10 dark:text-[#93C5FD]">
                    {{ __('settings.public_booking_generate') }}
                </button>
            </form>
        </div>
    </div>
</div>
