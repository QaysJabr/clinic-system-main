<x-app-layout>
@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
    $mode = old('account_mode', request('account_mode', 'new'));
@endphp

@if (! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif

<div class="mx-auto w-full max-w-3xl" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('doctor_onboarding.page_title') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('doctor_onboarding.page_subtitle') }}</p>
    </div>

    @if (session('info'))
        <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-100">{{ session('info') }}</div>
    @endif

    <form method="POST" action="{{ route('doctors.onboarding.store') }}" class="space-y-6" novalidate id="doctor-onboarding-form" data-existing-email-label="{{ __('doctor_onboarding.email_once_label') }}">
        @csrf

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctor_onboarding.section_identity') }}</h2>
            </div>
            <div class="space-y-4 p-4 sm:p-5">
                <fieldset class="m-0 border-0 p-0">
                    <legend class="sr-only">{{ __('doctor_onboarding.section_identity') }}</legend>
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold">
                            <input type="radio" name="account_mode" value="new" @checked($mode === 'new') class="text-[#0F4C81]">
                            {{ __('doctor_onboarding.account_mode_new') }}
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold">
                            <input type="radio" name="account_mode" value="existing" @checked($mode === 'existing') class="text-[#0F4C81]">
                            {{ __('doctor_onboarding.account_mode_existing') }}
                        </label>
                    </div>
                </fieldset>

                <div>
                    <label for="full_name" class="{{ $labelClass }}">{{ __('doctor_onboarding.label_full_name') }}</label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name', request('name')) }}" required class="{{ $inputClass }}">
                    @error('full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="onboarding-block-new" class="{{ $mode === 'existing' ? 'hidden' : '' }} space-y-4">
                    <div>
                        <label for="email" class="{{ $labelClass }}">{{ __('doctor_onboarding.email_once_label') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email', request('email')) }}" autocomplete="username"
                            class="{{ $inputClass }}" @if($mode !== 'existing') required @endif>
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('doctor_onboarding.email_once_hint') }}</p>
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="{{ $labelClass }}">{{ __('doctor_onboarding.label_password') }}</label>
                            <input type="password" name="password" id="password" autocomplete="new-password" class="{{ $inputClass }}">
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="{{ $labelClass }}">{{ __('doctor_onboarding.label_password_confirm') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="{{ $inputClass }}">
                        </div>
                    </div>
                </div>

                <div id="onboarding-block-existing" class="{{ $mode === 'existing' ? '' : 'hidden' }} space-y-2">
                    <label for="user_id" class="{{ $labelClass }}">{{ __('doctor_onboarding.label_pick_user') }}</label>
                    <select name="user_id" id="user_id" class="{{ $inputClass }}">
                        <option value="">{{ __('doctor_onboarding.placeholder_pick_user') }}</option>
                        @foreach ($linkableUsers as $u)
                            <option value="{{ $u->id }}" data-email="{{ $u->email }}" @selected((string) old('user_id', request('user_id')) === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p id="onboarding-existing-email" class="m-0 text-xs font-medium text-slate-600 dark:text-slate-400"></p>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/40 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
            <div class="border-b border-emerald-100 bg-emerald-50/80 px-4 py-4 dark:border-emerald-900/40 dark:bg-emerald-950/30 sm:px-5">
                <h2 class="m-0 text-base font-bold text-emerald-900 dark:text-emerald-200">{{ __('doctor_onboarding.section_clinical') }}</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
                <div>
                    <label for="phone" class="{{ $labelClass }}">{{ __('doctor_onboarding.label_phone') }}</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="{{ $inputClass }}">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="{{ $labelClass }}">{{ __('doctor_onboarding.label_status') }}</label>
                    <select name="status" id="status" required class="{{ $inputClass }}">
                        <option value="active" @selected(old('status', 'active') === 'active')>{{ __('doctor_onboarding.status_active') }}</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>{{ __('doctor_onboarding.status_inactive') }}</option>
                    </select>
                </div>
                <div>
                    <label for="specialty" class="{{ $labelClass }}">{{ __('doctors.field_specialty') }}</label>
                    <input type="text" name="specialty" id="specialty" value="{{ old('specialty') }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="room_number" class="{{ $labelClass }}">{{ __('doctors.field_room') }}</label>
                    <input type="text" name="room_number" id="room_number" value="{{ old('room_number') }}" class="{{ $inputClass }}">
                </div>
                <div class="sm:col-span-2">
                    <label for="license_number" class="{{ $labelClass }}">{{ __('doctors.field_license') }}</label>
                    <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}" class="{{ $inputClass }}">
                    @error('license_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="{{ $labelClass }}">{{ __('doctors.field_notes') }}</label>
                    <textarea name="notes" id="notes" rows="2" class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('doctors.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('doctor_onboarding.btn_back') }}</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6]">{{ __('doctor_onboarding.btn_save') }}</button>
        </div>
    </form>
</div>

@push('scripts')
    @vite('resources/js/doctor-onboarding.js')
@endpush
</x-app-layout>
