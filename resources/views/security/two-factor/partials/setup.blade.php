@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('profile.edit') }}" data-spa class="mb-3 inline-flex text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('profile.back_to_profile') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('security.two_factor_title') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('security.two_factor_scan') }}</p>
    </div>

    <x-profile.nav active="profile" />

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('profile.two_factor_setup_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex justify-center border-b border-gray-100 bg-gray-50/80 p-6 dark:border-[#374151] dark:bg-[#111827]/90">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#111827]">{!! $qrSvg !!}</div>
            </div>
            <div class="space-y-5 p-4 sm:p-6">
                <p class="m-0 break-all text-xs text-gray-500 dark:text-[#9CA3AF]"><strong class="text-gray-700 dark:text-[#E5E7EB]">{{ __('security.two_factor_secret') }}:</strong> {{ $secret }}</p>
                <form method="POST" action="{{ route('two-factor.setup.confirm') }}" class="max-w-sm space-y-4">
                    @csrf
                    <div>
                        <label for="two_factor_code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('security.two_factor_code') }}</label>
                        <input id="two_factor_code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required maxlength="6" class="{{ $inputClass }} @error('code') border-red-300 @enderror">
                        @error('code')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
