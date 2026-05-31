@php
    use App\Support\PlanDisplay;

    $authEntryUrl = auth()->check()
        ? (auth()->user()->can('view dashboard') ? route('dashboard') : route('profile.edit'))
        : null;
    $pricingUrl = Route::has('saas.pricing') ? route('saas.pricing') : '#';
    $startUrl = Route::has('saas.register-clinic.create')
        ? route('saas.register-clinic.create')
        : (Route::has('register') ? route('register') : (Route::has('login') ? route('login') : url('/')));
    $loginUrl = Route::has('login') ? route('login') : url('/');
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $dbPlans = collect($plans ?? []);
    $appName = config('app.name', 'Clinic System');
    $pageTitle = $appName.' — '.__('saas.landing_brand_subtitle');
    $metaDescription = __('saas.landing_meta_description');
    $canonicalUrl = url('/');
    $ogImageUrl = Route::has('og.image') ? route('og.image') : url('/og-image.png');
    $demoVideoEmbed = \App\Support\VideoEmbed::resolveEmbedUrl(
        config('marketing.demo_video_embed'),
        config('marketing.demo_video_url'),
    );
    $androidApkUrl = config('marketing.android_apk_url');
    $androidApkVersion = config('marketing.android_apk_version', '1.0.1');

    $featureTones = ['appointments', 'patients', 'invoices', 'reports', 'settings', 'staff'];
    $featureIcons = [
        '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0-12.75h.375m0 0h-.375m.375 0h.375"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="theme-color" content="#0F4C81">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <title>{{ $pageTitle }}</title>
    <script type="application/ld+json" @if(! empty($cspNonce ?? null)) nonce="{{ $cspNonce }}" @endif>
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $appName,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' => $metaDescription,
            'url' => $canonicalUrl,
            'inLanguage' => [app()->getLocale(), 'en', 'ar'],
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD', 'description' => __('saas.landing_pricing_subtitle')],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @include('partials.layout-vite-assets')
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body class="landing-page flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased dark:bg-[#0F172A] dark:text-slate-100">
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#0F4C81] focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white">{{ __('saas.landing_skip_to_content') }}</a>

<div id="locale-swap-root" class="flex min-h-screen flex-col">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3 no-underline">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#0F4C81] to-[#1F7A8C] text-lg font-bold text-white shadow-md shadow-blue-900/15">+</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-extrabold text-[#0F4C81] dark:text-[#93C5FD]">{{ $appName }}</span>
                    <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ __('saas.landing_brand_subtitle') }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-6 lg:flex" aria-label="{{ __('navigation.navigation') }}">
                <a href="#features" class="text-sm font-semibold text-slate-600 no-underline transition hover:text-[#0F4C81] dark:text-slate-300 dark:hover:text-[#93C5FD]">{{ __('saas.landing_nav_features') }}</a>
                <a href="#how" class="text-sm font-semibold text-slate-600 no-underline transition hover:text-[#0F4C81] dark:text-slate-300 dark:hover:text-[#93C5FD]">{{ __('saas.landing_nav_how') }}</a>
                <a href="#demo" class="text-sm font-semibold text-slate-600 no-underline transition hover:text-[#0F4C81] dark:text-slate-300 dark:hover:text-[#93C5FD]">{{ __('saas.landing_video_kicker') }}</a>
                <a href="#pricing" class="text-sm font-semibold text-slate-600 no-underline transition hover:text-[#0F4C81] dark:text-slate-300 dark:hover:text-[#93C5FD]">{{ __('saas.landing_nav_pricing') }}</a>
                <a href="#faq" class="text-sm font-semibold text-slate-600 no-underline transition hover:text-[#0F4C81] dark:text-slate-300 dark:hover:text-[#93C5FD]">{{ __('saas.landing_nav_faq') }}</a>
            </nav>

            <div class="flex items-center gap-2">
                <details class="relative lg:hidden [&_[open]_summary]:ring-2 [&_[open]_summary]:ring-[#0F4C81]/20">
                    <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200/80 bg-white shadow-sm dark:border-slate-600 dark:bg-slate-800 [&::-webkit-details-marker]:hidden" aria-label="{{ __('navigation.menu_mobile') }}">
                        <svg class="h-5 w-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </summary>
                    <div class="absolute end-0 top-full z-50 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                        <a href="#features" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 no-underline hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" onclick="this.closest('details')?.removeAttribute('open')">{{ __('saas.landing_nav_features') }}</a>
                        <a href="#how" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 no-underline hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" onclick="this.closest('details')?.removeAttribute('open')">{{ __('saas.landing_nav_how') }}</a>
                        <a href="#demo" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 no-underline hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" onclick="this.closest('details')?.removeAttribute('open')">{{ __('saas.landing_video_kicker') }}</a>
                        <a href="#pricing" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 no-underline hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" onclick="this.closest('details')?.removeAttribute('open')">{{ __('saas.landing_nav_pricing') }}</a>
                        <a href="#faq" class="block px-4 py-2.5 text-sm font-semibold text-slate-700 no-underline hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" onclick="this.closest('details')?.removeAttribute('open')">{{ __('saas.landing_nav_faq') }}</a>
                        @if ($androidApkUrl)
                            <a href="{{ $androidApkUrl }}" class="block border-t border-slate-100 px-4 py-2.5 text-sm font-bold text-emerald-700 no-underline hover:bg-emerald-50 dark:border-slate-800 dark:text-emerald-300 dark:hover:bg-emerald-950/40" rel="noopener" onclick="this.closest('details')?.removeAttribute('open')">{{ __('saas.landing_mobile_app_download') }}</a>
                        @endif
                    </div>
                </details>
                <x-locale-globe />
                <x-theme-toggle class="h-9 w-9 rounded-lg border border-slate-200/80 bg-white shadow-sm dark:border-slate-600 dark:bg-slate-800" />
                @auth
                    <a href="{{ $authEntryUrl }}" class="rounded-xl bg-[#0F4C81] px-4 py-2 text-xs font-bold text-white no-underline shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] sm:text-sm">{{ __('saas.enter_system') }}</a>
                @else
                    <a href="{{ $loginUrl }}" class="hidden rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-800 no-underline dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 sm:inline-flex sm:text-sm">{{ __('auth.login') }}</a>
                    <a href="{{ $startUrl }}" class="rounded-xl bg-[#0F4C81] px-4 py-2 text-xs font-bold text-white no-underline shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] sm:text-sm">{{ __('saas.landing_cta_start') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        {{-- Hero --}}
        <section class="relative overflow-hidden border-b border-slate-200/70 dark:border-slate-800">
            <div class="landing-orb landing-orb--a" aria-hidden="true"></div>
            <div class="landing-orb landing-orb--b" aria-hidden="true"></div>
            <div class="landing-orb landing-orb--c" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(15,76,129,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(15,76,129,0.04)_1px,transparent_1px)] bg-[size:28px_28px] dark:bg-[linear-gradient(to_right,rgba(59,130,246,0.06)_1px,transparent_1px),linear-gradient(to_bottom,rgba(59,130,246,0.06)_1px,transparent_1px)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(15,76,129,0.14),transparent_58%)] dark:bg-[radial-gradient(ellipse_at_top,_rgba(59,130,246,0.16),transparent_58%)]"></div>
            <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-8 lg:py-24">
                <div class="landing-reveal">
                    <p class="mb-4 inline-flex rounded-full border border-[#0F4C81]/20 bg-[#0F4C81]/5 px-3 py-1 text-xs font-bold text-[#0F4C81] dark:border-[#3B82F6]/30 dark:bg-[#3B82F6]/10 dark:text-[#93C5FD]">{{ __('saas.landing_kicker') }}</p>
                    <h1 class="m-0 text-3xl font-extrabold leading-[1.15] tracking-tight text-slate-900 dark:text-white sm:text-4xl lg:text-[2.75rem]">
                        {{ __('saas.landing_hero_title_before') }}
                        <span class="landing-gradient-text">{{ __('saas.landing_hero_title_highlight') }}</span>
                        {{ __('saas.landing_hero_title_after') }}
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 dark:text-slate-300 sm:text-lg">{{ __('saas.landing_hero_subtitle') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ $startUrl }}" class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-6 py-3.5 text-sm font-bold text-white no-underline shadow-lg shadow-blue-900/15 transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('saas.landing_cta_start') }}</a>
                        <a href="{{ $pricingUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-800 no-underline shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">{{ __('saas.landing_cta_pricing') }}</a>
                    </div>
                    <ul class="mt-8 flex flex-wrap gap-3">
                        @foreach ([__('saas.landing_trust_1'), __('saas.landing_trust_2'), __('saas.landing_trust_3')] as $trust)
                            <li class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300" aria-hidden="true">✓</span>
                                {{ $trust }}
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-6 space-y-3">
                        <p class="inline-flex max-w-full items-start gap-2 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-2 text-xs font-semibold leading-relaxed text-emerald-900 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200 sm:items-center">
                            <span aria-hidden="true" class="shrink-0">📱</span>
                            <span>{{ __('saas.landing_mobile_app_badge', ['version' => $androidApkVersion]) }} — {{ __('saas.landing_mobile_app_note') }}</span>
                        </p>
                        @if ($androidApkUrl)
                            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                <a href="{{ $androidApkUrl }}" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white no-underline shadow-sm transition hover:bg-emerald-700 sm:w-auto sm:py-2.5 sm:text-sm" rel="noopener">
                                    {{ __('saas.landing_mobile_app_download') }} v{{ $androidApkVersion }}
                                </a>
                                <span class="text-center text-xs font-medium text-slate-500 dark:text-slate-400 sm:text-start">{{ __('saas.landing_mobile_app_ios_soon') }}</span>
                            </div>
                            <p class="text-xs leading-relaxed text-amber-800 dark:text-amber-200/90">{{ __('saas.landing_mobile_app_update_hint') }}</p>
                        @else
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('saas.landing_mobile_app_ios_soon') }}</span>
                        @endif
                    </div>
                </div>

                {{-- App preview with mini sidebar --}}
                <div class="relative landing-reveal lg:translate-y-1">
                    <div class="landing-mock-glow absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-[#0F4C81]/25 to-cyan-500/10 blur-2xl dark:from-[#3B82F6]/25"></div>
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-2.5 dark:border-slate-800 dark:bg-slate-950">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ms-auto text-[10px] font-medium tabular-nums text-slate-400">{{ $appName }}</span>
                        </div>
                        <div class="flex min-h-[320px]">
                            <aside class="hidden w-[4.5rem] shrink-0 border-e border-slate-100 bg-slate-50/90 p-2 dark:border-slate-800 dark:bg-slate-950 sm:block" aria-hidden="true">
                                <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#0F4C81] to-[#1F7A8C] text-xs font-bold text-white">+</div>
                                @foreach ([['dashboard', __('saas.landing_mock_nav_dashboard'), true], ['patients', __('saas.landing_mock_nav_patients'), false], ['appointments', __('saas.landing_mock_nav_appointments'), false]] as [$navTone, $navLabel, $navActive])
                                    <div class="mb-1.5 flex flex-col items-center gap-0.5 rounded-lg px-1 py-1.5 {{ $navActive ? 'bg-[#0F4C81]/10 dark:bg-[#3B82F6]/15' : '' }}">
                                        <span class="sidebar-icon-wrap sidebar-icon-tone-{{ $navTone }} h-8 w-8">
                                            <svg class="sidebar-svg h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z"/></svg>
                                        </span>
                                        <span class="max-w-full truncate text-[8px] font-semibold text-slate-500 dark:text-slate-400">{{ $navLabel }}</span>
                                    </div>
                                @endforeach
                            </aside>
                            <div class="min-w-0 flex-1 p-4 sm:p-5">
                                <p class="m-0 text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('saas.landing_mock_title') }}</p>
                                <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.landing_mock_subtitle') }}</p>
                                <div class="mt-4 grid grid-cols-2 gap-2.5">
                                    <div class="rounded-xl border border-sky-100 bg-sky-50/80 p-3 dark:border-sky-900/40 dark:bg-sky-950/30">
                                        <p class="m-0 text-[10px] font-bold uppercase tracking-wide text-sky-800 dark:text-sky-300">{{ __('saas.landing_stat_appointments') }}</p>
                                        <p class="m-0 mt-1 text-xl font-extrabold tabular-nums text-sky-900 dark:text-sky-200">12</p>
                                    </div>
                                    <div class="rounded-xl border border-amber-100 bg-amber-50/80 p-3 dark:border-amber-900/40 dark:bg-amber-950/30">
                                        <p class="m-0 text-[10px] font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">{{ __('saas.landing_stat_pending') }}</p>
                                        <p class="m-0 mt-1 text-xl font-extrabold tabular-nums text-amber-950 dark:text-amber-200">5</p>
                                    </div>
                                    <div class="col-span-2 rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 dark:border-emerald-900/40 dark:bg-emerald-950/25">
                                        <p class="m-0 text-[10px] font-bold uppercase tracking-wide text-emerald-900 dark:text-emerald-300">{{ __('saas.landing_stat_collected') }}</p>
                                        <p class="m-0 mt-1 text-xl font-extrabold tabular-nums text-emerald-950 dark:text-emerald-200">4,850</p>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-1.5">
                                    @foreach ([['09:00', __('saas.landing_mock_patient_1')], ['10:30', __('saas.landing_mock_patient_2')], ['11:15', __('saas.landing_mock_patient_3')]] as [$time, $name])
                                        <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-100 bg-slate-50/80 px-2.5 py-2 dark:border-slate-800 dark:bg-slate-950/50">
                                            <span class="text-[11px] font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ $time }}</span>
                                            <span class="min-w-0 flex-1 truncate text-xs font-medium text-slate-700 dark:text-slate-200">{{ $name }}</span>
                                            <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">{{ __('saas.landing_mock_status') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Impact stats --}}
        <section class="border-b border-slate-200/70 bg-white py-14 dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="landing-reveal mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_stats_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-xl font-extrabold text-slate-900 dark:text-white sm:text-2xl">{{ __('saas.landing_stats_title') }}</h2>
                </div>
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([['saas.landing_stat_hours', 8, ''], ['saas.landing_stat_chaos', 90, '%'], ['saas.landing_stat_modules', 12, '+'], ['saas.landing_stat_langs', 2, '']] as [$labelKey, $count, $suffix])
                        <div class="landing-reveal landing-stat-card rounded-2xl border border-slate-200/90 bg-slate-50/80 p-5 text-center dark:border-slate-800 dark:bg-slate-900/50">
                            <p class="m-0 text-3xl font-extrabold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]" data-count-to="{{ $count }}" data-count-suffix="{{ $suffix }}">0{{ $suffix }}</p>
                            <p class="m-0 mt-2 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __($labelKey) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Clinic types marquee --}}
        <section class="overflow-hidden border-b border-slate-200/70 bg-slate-50 py-8 dark:border-slate-800 dark:bg-[#0F172A]" aria-label="{{ __('saas.landing_audience_kicker') }}">
            <p class="m-0 mb-6 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('saas.landing_audience_kicker') }}</p>
            <div class="relative flex overflow-hidden">
                <div class="landing-marquee gap-8 px-4">
                    @foreach (array_merge(__('saas.landing_audience_items'), __('saas.landing_audience_items')) as $audience)
                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-bold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <span class="h-2 w-2 rounded-full bg-[#1F7A8C]" aria-hidden="true"></span>
                            {{ $audience }}
                        </span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Value pillars --}}
        <section class="border-b border-slate-200/70 bg-white py-12 dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="m-0 text-center text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_pillars_kicker') }}</p>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @for ($p = 1; $p <= 4; $p++)
                        <div class="landing-reveal rounded-2xl border border-slate-200/90 bg-slate-50/50 p-5 dark:border-slate-800 dark:bg-slate-900/40">
                            <span class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#0F4C81]/10 text-sm font-extrabold text-[#0F4C81] dark:bg-[#3B82F6]/15 dark:text-[#93C5FD]">{{ $p }}</span>
                            <h3 class="m-0 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('saas.landing_pillar_'.$p.'_title') }}</h3>
                            <p class="m-0 mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('saas.landing_pillar_'.$p.'_desc') }}</p>
                        </div>
                    @endfor
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="scroll-mt-20 border-b border-slate-200/70 bg-slate-50 py-20 dark:border-slate-800 dark:bg-[#0F172A]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_nav_features') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_features_title') }}</h2>
                    <p class="m-0 mt-3 text-sm text-slate-600 dark:text-slate-300 sm:text-base">{{ __('saas.landing_features_subtitle') }}</p>
                </div>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @for ($i = 1; $i <= 6; $i++)
                        @php
                            $tone = $featureTones[$i - 1] ?? 'dashboard';
                            $iconPath = $featureIcons[$i - 1] ?? '';
                        @endphp
                        <article class="landing-reveal group rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0F4C81]/25 hover:shadow-lg hover:shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-[#3B82F6]/30">
                            <span class="sidebar-icon-wrap sidebar-icon-tone-{{ $tone }} mb-4 inline-flex h-11 w-11">
                                <svg class="sidebar-svg h-5 w-5 w-auto max-w-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">{!! $iconPath !!}</svg>
                            </span>
                            <h3 class="m-0 text-base font-extrabold text-slate-900 dark:text-white">{{ __('saas.landing_feature_'.$i.'_title') }}</h3>
                            <p class="m-0 mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('saas.landing_feature_'.$i.'_desc') }}</p>
                        </article>
                    @endfor
                </div>
            </div>
        </section>

        {{-- Before / After --}}
        <section class="border-b border-slate-200/70 bg-white py-20 dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_compare_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_compare_title') }}</h2>
                </div>
                <div class="mt-12 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-6 dark:border-slate-700 dark:bg-slate-900/40">
                        <h3 class="m-0 text-lg font-extrabold text-slate-500 dark:text-slate-400">{{ __('saas.landing_compare_old_title') }}</h3>
                        <ul class="m-0 mt-5 list-none space-y-3 p-0">
                            @for ($c = 1; $c <= 4; $c++)
                                <li class="flex gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 text-xs text-rose-700 dark:bg-rose-950/50 dark:text-rose-300" aria-hidden="true">✕</span>
                                    {{ __('saas.landing_compare_old_'.$c) }}
                                </li>
                            @endfor
                        </ul>
                    </div>
                    <div class="rounded-2xl border-2 border-[#0F4C81]/30 bg-gradient-to-br from-[#0F4C81]/5 to-white p-6 shadow-lg shadow-blue-900/5 dark:border-[#3B82F6]/40 dark:from-[#3B82F6]/10 dark:to-slate-900">
                        <h3 class="m-0 text-lg font-extrabold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_compare_new_title') }}</h3>
                        <ul class="m-0 mt-5 list-none space-y-3 p-0">
                            @for ($c = 1; $c <= 4; $c++)
                                <li class="flex gap-3 text-sm text-slate-700 dark:text-slate-200">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300" aria-hidden="true">✓</span>
                                    {{ __('saas.landing_compare_new_'.$c) }}
                                </li>
                            @endfor
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section id="how" class="scroll-mt-20 border-b border-slate-200/70 bg-slate-50 py-20 dark:border-slate-800 dark:bg-[#0F172A]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_steps_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_steps_title') }}</h2>
                </div>
                <ol class="relative mt-12 grid gap-6 md:grid-cols-3">
                    <div class="pointer-events-none absolute inset-x-0 top-10 hidden h-px bg-gradient-to-r from-transparent via-[#0F4C81]/30 to-transparent md:block" aria-hidden="true"></div>
                    @for ($step = 1; $step <= 3; $step++)
                        <li class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <span class="relative z-10 mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#0F4C81] text-sm font-extrabold text-white shadow-md dark:bg-[#3B82F6]">{{ $step }}</span>
                            <h3 class="m-0 text-lg font-extrabold text-slate-900 dark:text-white">{{ __('saas.landing_step_'.$step.'_title') }}</h3>
                            <p class="m-0 mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ __('saas.landing_step_'.$step.'_desc') }}</p>
                        </li>
                    @endfor
                </ol>
            </div>
        </section>

        {{-- Demo video --}}
        <section id="demo" class="scroll-mt-20 border-b border-slate-200/70 bg-gradient-to-b from-white to-slate-50 py-20 dark:border-slate-800 dark:from-slate-950 dark:to-[#0F172A]">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="landing-reveal mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_video_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_video_title') }}</h2>
                    <p class="m-0 mt-3 text-sm text-slate-600 dark:text-slate-300 sm:text-base">{{ __('saas.landing_video_subtitle') }}</p>
                </div>

                <div class="landing-reveal relative mt-10 overflow-hidden rounded-3xl border border-slate-200/90 bg-slate-900 shadow-2xl shadow-slate-900/20 dark:border-slate-700">
                    <div class="aspect-video w-full">
                        @if ($demoVideoEmbed)
                            <iframe
                                id="landing-demo-iframe"
                                class="h-full w-full"
                                src=""
                                data-embed-src="{{ $demoVideoEmbed }}"
                                title="{{ __('saas.landing_video_title') }}"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @else
                            <div class="flex h-full min-h-[220px] flex-col items-center justify-center bg-gradient-to-br from-[#0F4C81] via-[#0c3d66] to-slate-900 px-6 text-center sm:min-h-[280px]">
                                <a
                                    href="{{ $startUrl }}"
                                    class="landing-video-play mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white/15 text-white no-underline ring-2 ring-white/30 backdrop-blur transition hover:scale-105 hover:bg-white/25"
                                    aria-label="{{ __('saas.landing_video_cta_start') }}"
                                >
                                    <svg class="ms-1 h-10 w-10 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                </a>
                                <p class="m-0 text-lg font-extrabold text-white">{{ __('saas.landing_video_placeholder_title') }}</p>
                                <p class="m-0 mt-2 max-w-md text-sm text-blue-100">{{ __('saas.landing_video_placeholder_desc') }}</p>
                            </div>
                        @endif
                    </div>
                    @if ($demoVideoEmbed)
                        <button
                            type="button"
                            class="absolute inset-0 flex items-center justify-center bg-slate-900/40 transition hover:bg-slate-900/25"
                            data-landing-video-open
                            aria-label="{{ __('saas.landing_video_play') }}"
                        >
                            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-white/90 text-[#0F4C81] shadow-xl transition hover:scale-105">
                                <svg class="ms-1 h-10 w-10 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                        </button>
                    @endif
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    @if ($demoVideoEmbed)
                        <button type="button" data-landing-video-open class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-[#0c3d66] dark:bg-[#3B82F6]">
                            {{ __('saas.landing_video_play') }}
                        </button>
                    @endif
                    <a href="{{ $startUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-800 no-underline shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                        {{ __('saas.landing_video_cta_start') }}
                    </a>
                </div>
            </div>
        </section>

        <div id="landing-video-modal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-950/85 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="{{ __('saas.landing_video_title') }}">
            <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-700 bg-black shadow-2xl">
                <button type="button" class="absolute end-3 top-3 z-10 rounded-lg bg-black/60 px-3 py-1.5 text-xs font-bold text-white backdrop-blur hover:bg-black/80" data-landing-video-close>
                    {{ __('saas.landing_video_modal_close') }}
                </button>
                <div class="aspect-video w-full">
                    <iframe id="landing-video-modal-iframe" class="h-full w-full" src="" title="{{ __('saas.landing_video_title') }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </div>

        {{-- Security --}}
        <section class="border-b border-slate-200/70 bg-white py-16 dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_security_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_security_title') }}</h2>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @for ($s = 1; $s <= 3; $s++)
                        <div class="rounded-2xl border border-slate-200/90 bg-slate-50/50 p-6 text-center dark:border-slate-800 dark:bg-slate-900/40">
                            <span class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0F4C81]/10 dark:bg-[#3B82F6]/15">
                                <svg class="h-6 w-6 text-[#0F4C81] dark:text-[#93C5FD]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    @if ($s === 1)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                    @elseif ($s === 2)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375"/>
                                    @endif
                                </svg>
                            </span>
                            <h3 class="m-0 text-base font-extrabold text-slate-900 dark:text-white">{{ __('saas.landing_security_'.$s.'_title') }}</h3>
                            <p class="m-0 mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('saas.landing_security_'.$s.'_desc') }}</p>
                        </div>
                    @endfor
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section id="pricing" class="scroll-mt-20 border-b border-slate-200/70 bg-slate-50 py-20 dark:border-slate-800 dark:bg-[#0F172A]">
            <div id="saas-pricing-root" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" data-default-cycle="monthly">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.pricing_kicker') }}</p>
                        <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_pricing_title') }}</h2>
                        <p class="m-0 mt-2 max-w-xl text-sm text-slate-600 dark:text-slate-300 sm:text-base">{{ __('saas.landing_pricing_subtitle') }}</p>
                    </div>
                    <a href="{{ $pricingUrl }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 no-underline shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">{{ __('saas.landing_cta_pricing') }}</a>
                </div>

                @if ($dbPlans->isNotEmpty())
                    <div class="relative z-20 mt-8 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('saas.pricing_cycle') }}</span>
                        <button type="button" data-cycle-set="monthly" class="rounded-lg border border-[#0F4C81] bg-[#0F4C81] px-3 py-1.5 text-xs font-bold text-white ring-2 ring-[#0F4C81] dark:bg-[#3B82F6] dark:ring-[#3B82F6]">{{ __('common.monthly') }}</button>
                        <button type="button" data-cycle-set="yearly" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-800 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">{{ __('common.yearly') }}</button>
                    </div>
                    <div class="mt-8 grid gap-5 lg:grid-cols-3">
                        @foreach ($dbPlans as $plan)
                            @php
                                $features = is_array($plan->features) ? array_slice($plan->features, 0, 4) : [];
                                $isPopular = str_contains(strtolower((string) $plan->name), 'pro') || str_contains(strtolower((string) ($plan->slug ?? '')), 'pro');
                            @endphp
                            <div class="relative flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/60 {{ $isPopular ? 'ring-2 ring-[#0F4C81] dark:ring-[#3B82F6]' : '' }}">
                                @if ($isPopular)
                                    <span class="pointer-events-none absolute -top-3 start-4 rounded-full bg-[#0F4C81] px-3 py-1 text-[10px] font-bold text-white dark:bg-[#3B82F6]">{{ __('saas.pricing_popular_badge') }}</span>
                                @endif
                                <p class="m-0 text-lg font-extrabold text-[#0F4C81] dark:text-[#93C5FD]">{{ PlanDisplay::localizedName($plan) }}</p>
                                <div data-price-panel="monthly" class="mt-2">
                                    <p class="m-0 text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">{{ $plan->displayMonthly() }}</p>
                                    <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.pricing_per_month') }}</p>
                                </div>
                                <div data-price-panel="yearly" class="mt-2 hidden">
                                    <p class="m-0 text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">{{ $plan->displayYearly() }}</p>
                                    <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.pricing_per_year') }}</p>
                                </div>
                                @if (count($features))
                                    <ul class="m-0 mt-5 flex-1 list-none space-y-2 p-0 text-sm text-slate-700 dark:text-slate-200">
                                        @foreach ($features as $feature)
                                            <li class="flex gap-2">
                                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#0F4C81] dark:bg-[#60A5FA]" aria-hidden="true"></span>
                                                <span>{{ PlanDisplay::localizedFeature($feature) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <a href="{{ $startUrl }}" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-[#0F4C81] px-4 py-3 text-sm font-bold text-white no-underline transition hover:bg-[#0c3d66] dark:bg-[#3B82F6]">{{ __('saas.landing_plan_cta') }}</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-10 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300">{{ __('saas.landing_no_plans') }}</p>
                @endif
                <p class="m-0 mt-6 text-center text-xs text-slate-500 dark:text-slate-400">{{ __('saas.pricing_trust_note') }}</p>
            </div>
        </section>

        {{-- Testimonials --}}
        <section class="border-b border-slate-200/70 bg-slate-50 py-20 dark:border-slate-800 dark:bg-[#0F172A]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="landing-reveal mx-auto max-w-2xl text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_testimonials_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_testimonials_title') }}</h2>
                </div>
                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @for ($t = 1; $t <= 3; $t++)
                        <blockquote class="landing-reveal landing-testimonial m-0 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                            <div class="mb-4 flex gap-0.5 text-amber-400" aria-hidden="true">
                                @for ($star = 0; $star < 5; $star++)
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="m-0 text-sm leading-relaxed text-slate-700 dark:text-slate-200">«{{ __('saas.landing_testimonial_'.$t.'_quote') }}»</p>
                            <footer class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('saas.landing_testimonial_'.$t.'_name') }}</p>
                                <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.landing_testimonial_'.$t.'_role') }}</p>
                            </footer>
                        </blockquote>
                    @endfor
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="scroll-mt-20 border-b border-slate-200/70 bg-white py-20 dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.landing_faq_kicker') }}</p>
                    <h2 class="m-0 mt-2 text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">{{ __('saas.landing_faq_title') }}</h2>
                    <p class="m-0 mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('saas.landing_faq_subtitle') }}</p>
                </div>
                <div class="mt-10 space-y-3">
                    @for ($f = 1; $f <= 4; $f++)
                        <details class="group rounded-2xl border border-slate-200 bg-slate-50/50 open:bg-white open:shadow-sm dark:border-slate-700 dark:bg-slate-900/40 dark:open:bg-slate-900">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-extrabold text-slate-900 dark:text-white [&::-webkit-details-marker]:hidden">
                                {{ __('saas.landing_faq_'.$f.'_q') }}
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition group-open:rotate-180 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </summary>
                            <div class="border-t border-slate-200/80 px-5 pb-4 pt-3 text-sm leading-relaxed text-slate-600 dark:border-slate-700 dark:text-slate-300">
                                {{ __('saas.landing_faq_'.$f.'_a') }}
                            </div>
                        </details>
                    @endfor
                </div>
            </div>
        </section>

        {{-- Final CTA --}}
        <section class="py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#0F4C81] via-[#0c3d66] to-slate-900 px-8 py-14 text-center shadow-xl dark:from-[#1e3a5f] dark:via-[#0F4C81] dark:to-slate-950">
                    <h2 class="m-0 text-2xl font-extrabold text-white sm:text-3xl">{{ __('saas.landing_final_title') }}</h2>
                    <p class="mx-auto m-0 mt-4 max-w-xl text-sm text-blue-100 sm:text-base">{{ __('saas.landing_final_subtitle') }}</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a href="{{ $startUrl }}" class="rounded-xl bg-white px-7 py-3.5 text-sm font-extrabold text-[#0F4C81] no-underline shadow-lg transition hover:bg-blue-50">{{ __('saas.landing_cta_start') }}</a>
                        <a href="{{ $loginUrl }}" class="rounded-xl border border-white/30 bg-white/10 px-7 py-3.5 text-sm font-extrabold text-white no-underline backdrop-blur transition hover:bg-white/20">{{ __('auth.login') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="mt-auto border-t border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-10 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div>
                <p class="m-0 text-sm font-bold text-slate-800 dark:text-slate-100">© {{ date('Y') }} {{ $appName }}</p>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.landing_footer_note') }}</p>
            </div>
            <div class="flex flex-wrap gap-4 text-sm font-semibold">
                @if (Route::has('legal.privacy'))
                    <a href="{{ route('legal.privacy') }}" class="text-slate-600 no-underline hover:underline dark:text-slate-300">{{ __('legal.privacy_nav') }}</a>
                    <a href="{{ route('legal.terms') }}" class="text-slate-600 no-underline hover:underline dark:text-slate-300">{{ __('legal.terms_nav') }}</a>
                @endif
                @if (Route::has('contact'))
                    <a href="{{ route('contact') }}" class="text-slate-600 no-underline hover:underline dark:text-slate-300">{{ __('contact.title') }}</a>
                @endif
                <a href="#faq" class="text-slate-600 no-underline hover:underline dark:text-slate-300">{{ __('saas.landing_nav_faq') }}</a>
                <a href="{{ $pricingUrl }}" class="text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]">{{ __('saas.pricing_nav') }}</a>
                <a href="{{ $startUrl }}" class="text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]">{{ __('saas.pricing_register_clinic') }}</a>
                <a href="{{ $loginUrl }}" class="text-slate-600 no-underline hover:underline dark:text-slate-300">{{ __('auth.login') }}</a>
            </div>
        </div>
    </footer>

    @guest
        <div class="pointer-events-none fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/90 bg-white/95 p-3 backdrop-blur-md dark:border-slate-700 dark:bg-slate-950/95 sm:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            <div class="pointer-events-auto mx-auto flex max-w-lg flex-col gap-2">
                @if ($androidApkUrl)
                    <a href="{{ $androidApkUrl }}" class="w-full rounded-xl bg-emerald-600 py-3.5 text-center text-sm font-bold text-white no-underline shadow-sm transition hover:bg-emerald-700" rel="noopener">
                        {{ __('saas.landing_mobile_app_download') }} v{{ $androidApkVersion }}
                    </a>
                @endif
                <div class="flex gap-2">
                    <a href="{{ $pricingUrl }}" class="flex-1 rounded-xl border border-slate-300 bg-white py-3 text-center text-sm font-bold text-slate-800 no-underline dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">{{ __('saas.landing_cta_pricing') }}</a>
                    <a href="{{ $startUrl }}" class="flex-1 rounded-xl bg-[#0F4C81] py-3 text-center text-sm font-bold text-white no-underline dark:bg-[#3B82F6]">{{ __('saas.landing_mobile_cta') }}</a>
                </div>
            </div>
        </div>
        <div class="@if ($androidApkUrl) h-28 @else h-20 @endif sm:hidden" aria-hidden="true"></div>
    @endguest

    @include('partials.js-i18n')
</div>
</body>
</html>
