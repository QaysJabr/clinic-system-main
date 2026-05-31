<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}" class="h-full scroll-smooth">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('ui.system_name') }}</title>
        @include('partials.layout-vite-assets')
    </head>
    <body class="app-shell bg-[#F8F9FA] text-[#1F2937] transition-colors duration-200 dark:bg-[#0F172A] dark:text-[#F3F4F6]">
        <aside id="sidebar" class="app-sidebar flex flex-col overflow-hidden bg-white dark:bg-[#111827]" aria-label="{{ __('navigation.sidebar_platform_aria') }}">
                <!-- LOGO -->
                <div class="border-b border-slate-100 px-5 pb-4 pt-6 dark:border-[#374151]">
                    <a href="{{ route('platform.dashboard') }}" data-spa class="group flex items-center gap-3 no-underline">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-700 to-[#0F4C81] shadow-md shadow-violet-900/20">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="m-0 text-sm font-bold leading-tight text-violet-800 dark:text-violet-300">{{ __('navigation.platform_name') }}</p>
                            <p class="m-0 mt-0.5 truncate text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('navigation.platform_tagline') }}</p>
                        </div>
                    </a>
                </div>

                <!-- NAVIGATION -->
                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-3 py-4 pb-6">
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.platform_section') }}</p>

                    <a href="{{ route('platform.dashboard') }}" data-spa class="sidebar-link {{ request()->routeIs('platform.dashboard') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-dashboard"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.platform_dashboard') }}</span>
                    </a>

                    <a href="{{ route('platform.clinics.index') }}" data-spa class="sidebar-link {{ request()->routeIs('platform.clinics.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-clinics"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M3 21h18M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.sidebar_clinics') }}</span>
                    </a>

                    <a href="{{ route('admin.plans.index') }}" data-spa class="sidebar-link {{ request()->routeIs('admin.plans.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-plans"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.sidebar_subscription_plans') }}</span>
                    </a>

                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.section_account') }}</p>
                    <a href="{{ route('profile.edit') }}" data-spa class="sidebar-link {{ request()->routeIs('profile.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-profile"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.profile') }}</span>
                    </a>
                </nav>
        </aside>

        <div id="locale-swap-root" class="app-shell-main">
            <div class="main-with-sidebar flex min-h-screen w-full min-w-0 flex-col">
                <!-- TOP BAR -->
                <header class="app-topbar" aria-label="{{ __('navigation.navbar_aria_main') }}">
                    <div class="app-topbar-start">
                        <button type="button" id="mobile-menu-btn" class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#CBD5E1] dark:hover:bg-[#374151] lg:hidden" aria-label="{{ __('navigation.menu_mobile') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <span class="app-topbar-title">{{ __('ui.system_name') }}</span>
                        <span class="shrink-0 rounded-full border border-violet-200 bg-violet-50 px-2 py-0.5 text-[11px] font-bold text-violet-800 dark:border-violet-800/50 dark:bg-violet-950/40 dark:text-violet-200">{{ __('navigation.header_platform_badge') }}</span>
                    </div>

                    <div class="app-topbar-end">
                        <x-locale-globe />
                        <x-theme-toggle />
                        <x-header-notifications-dropdown
                            :notifications="$headerNotifications ?? []"
                            :unread-count="(int) ($unreadNotificationsCount ?? 0)"
                            :index-route="$headerNotificationsIndexRoute ?? null"
                        />
                        <details
                            id="header-user-menu"
                            data-header-popover
                            data-header-user-menu
                            class="relative z-40 [&[open]>summary]:border-[#0F4C81]/35 [&[open]>summary]:bg-slate-50/90 [&[open]>summary]:shadow-md [&[open]>summary]:ring-2 [&[open]>summary]:ring-[#0F4C81]/20 dark:[&[open]>summary]:border-[#3B82F6]/40 dark:[&[open]>summary]:bg-[#111827] dark:[&[open]>summary]:ring-[#3B82F6]/25 [&[open]>summary_.header-user-menu-chevron]:rotate-180"
                        >
                            <summary
                                id="header-user-menu-trigger"
                                aria-controls="header-user-menu-panel"
                                aria-haspopup="true"
                                class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-slate-200/90 bg-white py-1 ps-1 pe-2 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:hover:border-slate-600 dark:hover:bg-[#374151] sm:pe-3 [&::-webkit-details-marker]:hidden"
                                aria-label="{{ __('navigation.account_menu_aria') }}"
                            >
                                <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ __('ui.user_avatar_alt', ['name' => Auth::user()->name]) }}" class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-white dark:ring-[#374151]" width="36" height="36">
                                <span class="hidden max-w-[120px] truncate text-sm font-semibold text-slate-800 dark:text-[#F3F4F6] sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="header-user-menu-chevron hidden h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 dark:text-slate-500 sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div
                                id="header-user-menu-panel"
                                role="menu"
                                aria-labelledby="header-user-menu-trigger"
                                class="absolute end-0 top-full z-50 mt-2 w-[min(calc(100vw-2rem),17rem)] origin-top overflow-hidden rounded-xl border border-slate-200/90 bg-white py-1 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5 dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-black/40 dark:ring-white/5"
                            >
                                <div class="border-b border-slate-100 bg-gradient-to-l from-slate-50/80 to-white px-4 py-3 dark:border-[#374151] dark:from-[#111827] dark:to-[#1F2937]">
                                    <p class="m-0 truncate text-sm font-bold text-slate-900 dark:text-[#F3F4F6]">{{ Auth::user()->name }}</p>
                                    <p class="m-0 mt-1 truncate text-xs text-slate-500 dark:text-[#9CA3AF]">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" data-spa role="menuitem" onclick="this.closest('details')?.removeAttribute('open')" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 no-underline transition-colors hover:bg-slate-100 hover:text-[#0F4C81] dark:text-[#E5E7EB] dark:hover:bg-[#111827] dark:hover:text-[#93C5FD]">
                                    <svg class="h-5 w-5 shrink-0 text-[#0F4C81]/80" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                    {{ __('navigation.profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 p-2 dark:border-[#374151]" role="none">
                                    @csrf
                                    <button type="submit" role="menuitem" class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-100 bg-red-50/90 px-3 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 active:scale-[0.99] dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-950/60">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                                        {{ __('navigation.logout') }}
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                </header>

                <!-- PAGE CONTENT -->
                <main id="app-content" class="relative z-0 flex-1 overflow-auto bg-[#F8F9FA] px-4 py-6 dark:bg-[#0F172A] sm:px-6 lg:px-8 lg:py-8" aria-label="{{ __('navigation.main_content_aria') }}">
                    <div class="app-content-inner">
                        @isset($header)
                            <div class="app-page-header">{{ $header }}</div>
                        @endisset
                        @include('partials.flash-session')

                        {{ $slot }}
                    </div>
                </main>
            </div>
            @include('partials.js-i18n')
        </div>

        @include('partials.confirm-dialog')

        <div id="spa-loader" class="spa-progress-bar pointer-events-none fixed start-0 top-0 z-[60] h-[2px] w-0 bg-[#0F4C81] opacity-0 shadow-none transition-[width,opacity] duration-200 ease-out dark:bg-[#60A5FA]" aria-hidden="true"></div>

        @vite(['resources/js/platform.js'])
        @stack('scripts')
    </body>
</html>
