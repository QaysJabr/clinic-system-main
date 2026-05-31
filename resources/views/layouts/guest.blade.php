<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}" class="h-full scroll-smooth overflow-x-hidden transition-colors duration-200 [color-scheme:light] dark:[color-scheme:dark]">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $guestTitle = $pageTitle ?? __('ui.system_name');
            $guestDescription = $metaDescription ?? __('saas.landing_meta_description');
            $ogImageUrl = Route::has('og.image') ? route('og.image') : url('/og-image.png');
        @endphp
        <meta name="description" content="{{ $guestDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $guestTitle }}">
        <meta property="og:description" content="{{ $guestDescription }}">
        <meta property="og:image" content="{{ $ogImageUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $guestTitle }}">
        <meta name="twitter:description" content="{{ $guestDescription }}">
        <meta name="twitter:image" content="{{ $ogImageUrl }}">

        <title>{{ $guestTitle }}</title>

        @include('partials.layout-vite-assets')
    </head>
    @php
        $isWide = ! empty($wide);
    @endphp
    <body
        @class([
            'guest-layout-body min-h-dvh overflow-x-hidden font-sans antialiased transition-colors duration-200',
            'guest-auth-body' => ! $isWide,
        ])
    >
        <div id="locale-swap-root">
        @if ($isWide)
            <div class="pointer-events-none fixed inset-0 -z-10 bg-slate-50 dark:bg-slate-950"></div>

            <div class="absolute start-4 top-4 z-30 flex items-center gap-3 sm:start-6 sm:top-6">
                <a href="{{ url('/') }}" class="text-xs font-semibold text-slate-600 no-underline transition hover:text-teal-800 dark:text-slate-400 dark:hover:text-teal-400">{{ __('ui.home') }}</a>
                <x-locale-globe summary-class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200/80 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 [&::-webkit-details-marker]:hidden" />
                <x-theme-toggle class="h-9 w-9 rounded-lg border border-slate-200/80 bg-white shadow-sm dark:border-slate-600 dark:bg-slate-800" />
            </div>

            <div class="flex min-h-dvh flex-col items-center justify-center px-4 py-10 sm:px-6 sm:py-12">
                <div class="w-full max-w-6xl">
                    <div
                        @class([
                            'rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-600 dark:bg-slate-900 sm:p-8',
                            'auth-shake' => ! empty($shakeOnError) && $errors->any(),
                        ])
                    >
                        @include('partials.flash-session')
                        {{ $slot }}
                    </div>
                    @if (Route::has('legal.privacy'))
                        <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
                            <a href="{{ route('legal.privacy') }}" class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('legal.privacy_nav') }}</a>
                            <span class="mx-2" aria-hidden="true">·</span>
                            <a href="{{ route('legal.terms') }}" class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('legal.terms_nav') }}</a>
                            @if (Route::has('contact'))
                                <span class="mx-2" aria-hidden="true">·</span>
                                <a href="{{ route('contact') }}" class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('contact.title') }}</a>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        @else
            <div class="auth-page-in grid min-h-dvh lg:grid-cols-2">
                @include('partials.auth-split-aside')

                <div class="relative flex min-h-dvh flex-col bg-slate-50 dark:bg-[#0F172A]">
                    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(15,76,129,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(15,76,129,0.03)_1px,transparent_1px)] bg-[size:28px_28px] dark:bg-[linear-gradient(to_right,rgba(59,130,246,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(59,130,246,0.05)_1px,transparent_1px)]"></div>

                    <div class="relative z-30 flex items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8" style="padding-top: max(1rem, env(safe-area-inset-top, 0px));">
                        <a href="{{ url('/') }}" class="inline-flex min-h-9 items-center rounded-lg px-2 text-xs font-semibold text-slate-600 no-underline transition hover:text-[#0F4C81] dark:text-slate-300 dark:hover:text-[#93C5FD]">{{ __('ui.home') }}</a>
                        <div class="flex items-center gap-2">
                            <x-locale-globe summary-class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200/80 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 [&::-webkit-details-marker]:hidden" />
                            <x-theme-toggle class="h-9 w-9 rounded-lg border border-slate-200/80 bg-white shadow-sm dark:border-slate-600 dark:bg-slate-800" />
                        </div>
                    </div>

                    <div class="relative flex flex-1 flex-col items-center justify-center px-4 pb-8 sm:px-6 lg:px-10" style="padding-bottom: max(2rem, env(safe-area-inset-bottom, 0px));">
                        <div
                            @class([
                                'auth-card-shell auth-form-shell auth-card-in w-full rounded-2xl border px-6 py-7 shadow-xl shadow-slate-900/5 sm:px-8 sm:py-8 dark:shadow-black/30',
                                'auth-form-shell--wide' => ! empty($wideForm),
                                'auth-shake' => ! empty($shakeOnError) && $errors->any(),
                            ])
                        >
                            @include('partials.auth-brand-header')
                            @include('partials.flash-session')
                            {{ $slot }}
                        </div>

                        <p class="auth-form-shell mt-5 text-center text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ config('app.name', 'Clinic System') }} · {{ __('auth.encrypted_connection') }}
                            @if (Route::has('legal.privacy'))
                                · <a href="{{ route('legal.privacy') }}" class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('legal.privacy_nav') }}</a>
                                · <a href="{{ route('legal.terms') }}" class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('legal.terms_nav') }}</a>
                                @if (Route::has('contact'))
                                    · <a href="{{ route('contact') }}" class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('contact.title') }}</a>
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @include('partials.js-i18n')
        </div>
        @stack('scripts')
    </body>
</html>
