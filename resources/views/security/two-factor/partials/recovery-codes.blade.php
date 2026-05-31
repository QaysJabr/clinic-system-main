@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('profile.edit') }}" data-spa class="mb-3 inline-flex text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('profile.back_to_profile') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('security.two_factor_recovery_title') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('security.two_factor_recovery_intro') }}</p>
    </div>

    <x-profile.nav active="profile" />

    <div class="mb-6 overflow-hidden rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-sm text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100" role="alert">
        {{ __('profile.recovery_codes_warning') }}
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('profile.recovery_codes_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            @if (count($codes) > 0)
                <ul class="m-0 grid list-none gap-2 p-4 font-mono text-sm sm:grid-cols-2 sm:p-6">
                    @foreach ($codes as $code)
                        <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-[#374151] dark:bg-[#111827]">{{ $code }}</li>
                    @endforeach
                </ul>
            @else
                <p class="m-0 px-4 py-10 text-center text-sm text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('profile.recovery_codes_empty') }}</p>
            @endif
            <div class="border-t border-gray-100 px-4 py-4 dark:border-[#374151] sm:px-6">
                <a href="{{ route('profile.edit') }}" data-spa class="inline-flex rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('profile.back_to_profile') }}</a>
            </div>
        </div>
    </div>
</div>
