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
        <aside id="sidebar" class="app-sidebar flex flex-col overflow-hidden bg-white dark:bg-[#111827]" aria-label="{{ __('navigation.section_menu') }}">
                <!-- LOGO -->
                <div class="border-b border-slate-100 px-5 pb-4 pt-6 dark:border-[#374151]">
                    <a
                        href="{{ auth()->user()->hasRole('super_admin') ? route('platform.dashboard') : (auth()->user()->can('view dashboard') ? route('dashboard') : route('profile.edit')) }}"
                        @if(! auth()->user()->hasRole('super_admin') && auth()->user()->can('view dashboard')) data-spa @endif
                        class="group flex items-center gap-3 no-underline"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0F4C81] to-[#1F7A8C] shadow-md shadow-blue-900/10">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-9h6m-6 3h6m-6 3h6M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="m-0 text-sm font-bold leading-tight text-[#0F4C81] dark:text-[#93C5FD]">{{ __('navigation.brand_clinic') }}</p>
                            <p class="m-0 mt-0.5 truncate text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('navigation.brand_subtitle') }}</p>
                        </div>
                    </a>
                </div>

                <!-- NAVIGATION -->
                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-3 py-4 pb-6">
                    @php
                        $linkedDoctorForNav = auth()->user()->linkedDoctor();
                        $unreadForBadge = (int) ($unreadNotificationsCount ?? 0);
                        $showUnreadNotifBadge = auth()->user()->can('view notifications') && $unreadForBadge > 0;
                        $unreadNotifDisplay = $unreadForBadge > 99 ? '99+' : (string) $unreadForBadge;
                    @endphp
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.section_menu') }}</p>

                    @if (! auth()->user()->hasRole('super_admin') && auth()->user()->can('view dashboard'))
                    <a href="{{ route('dashboard') }}" data-spa class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-dashboard"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.dashboard') }}</span>
                    </a>
                    @endif

                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.section_data') }}</p>

                    @can('manage patients')
                    <a href="{{ route('patients.index') }}" data-spa class="sidebar-link {{ request()->routeIs('patients.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-patients"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></span>
                        <span class="sidebar-label">{{ __('patients.title') }}</span>
                    </a>
                    @endcan

                    @can('manage doctors')
                    <a href="{{ route('doctors.index') }}" data-spa class="sidebar-link {{ request()->routeIs('doctors.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-doctors"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg></span>
                        <span class="sidebar-label">{{ __('doctors.title') }}</span>
                    </a>
                    @endcan

                    @can('manage appointments')
                    <a href="{{ route('appointments.index') }}" data-spa class="sidebar-link {{ request()->routeIs('appointments.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-appointments"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/></svg></span>
                        <span class="sidebar-label">{{ __('appointments.nav_appointments') }}</span>
                    </a>
                    @endcan

                    @can('manage visits')
                    <a href="{{ route('visits.index') }}" data-spa class="sidebar-link {{ request()->routeIs('visits.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-visits"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg></span>
                        <span class="sidebar-label">{{ __('visits.nav_visits_sidebar') }}</span>
                    </a>
                    @endcan

                    @can('manage staff')
                    <a href="{{ route('staff.index') }}" data-spa class="sidebar-link {{ request()->routeIs('staff.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-staff"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.09 9.09 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.staff') }}</span>
                    </a>
                    @endcan

                    @if(auth()->user()->can('manage invoices') || auth()->user()->can('manage expenses') || auth()->user()->can('manage expense categories') || auth()->user()->can('manage inventory') || auth()->user()->can('view inventory') || auth()->user()->can('manage payroll') || auth()->user()->can('manage staff payroll') || auth()->user()->can('view doctor earnings') || auth()->user()->can('manage doctor earnings'))
                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.section_finance') }}</p>
                    @can('manage invoices')
                    <a href="{{ route('invoices.index') }}" data-spa class="sidebar-link {{ request()->routeIs('invoices.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-invoices"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0-12.75h.375m0 0h-.375m.375 0h.375"/></svg></span>
                        <span class="sidebar-label">{{ __('invoices.title') }}</span>
                    </a>
                    @endcan
                    @can('manage expenses')
                    <a href="{{ route('expenses.index') }}" data-spa class="sidebar-link {{ request()->routeIs('expenses.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-expenses"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg></span>
                        <span class="sidebar-label">{{ __('expenses.nav') }}</span>
                    </a>
                    @endcan
                    @canany(['manage inventory', 'view inventory'])
                    <a href="{{ route('inventory.dashboard') }}" data-spa class="sidebar-link {{ request()->routeIs('inventory.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-inventory"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10.5 11.25h3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg></span>
                        <span class="sidebar-label">{{ __('inventory.nav') }}</span>
                    </a>
                    @endcan
                    @can('manage expense categories')
                    <a href="{{ route('expense-categories.index') }}" data-spa class="sidebar-link {{ request()->routeIs('expense-categories.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-categories"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25A2.25 2.25 0 018.25 10.5H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></span>
                        <span class="sidebar-label">{{ __('expenses.nav_categories') }}</span>
                    </a>
                    @endcan
                    @can('manage staff payroll')
                    <a href="{{ route('staff-compensation-profiles.index') }}" data-spa class="sidebar-link {{ request()->routeIs('staff-compensation-profiles.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-compensation"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.653a2.548 2.548 0 010-3.286l1.006-1.225a2.25 2.25 0 013.182 0l2.575 3.129M11.42 15.17L9.15 12.9"/></svg></span>
                        <span class="sidebar-label">{{ __('payroll.link_compensation_profiles') }}</span>
                    </a>
                    @endcan
                    @can('manage payroll')
                    <a href="{{ route('staff-payments.index') }}" data-spa class="sidebar-link {{ request()->routeIs('staff-payments.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-payroll"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-9h6m-6 3h6M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg></span>
                        <span class="sidebar-label">{{ __('payroll.nav_sidebar') }}</span>
                    </a>
                    @endcan
                    @canany(['view doctor earnings', 'manage doctor earnings'])
                    <a href="{{ route('doctor-earnings.index') }}" data-spa class="sidebar-link {{ request()->routeIs('doctor-earnings.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-earnings"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l2.25 2.25L21.75 3M21.75 3v6.75M15 3h6.75"/></svg></span>
                        <span class="sidebar-label">{{ auth()->user()->hasRole('doctor') && ! auth()->user()->hasRole('admin') ? __('navigation.sidebar_doctor_earnings_own') : __('navigation.sidebar_doctor_earnings_admin') }}</span>
                    </a>
                    @endcanany
                    @if($linkedDoctorForNav)
                    <a href="{{ route('reports.doctors.show', $linkedDoctorForNav) }}" data-spa class="sidebar-link {{ request()->routeIs('reports.doctors.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-chart"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.sidebar_my_financial_report') }}</span>
                    </a>
                    @endif
                    @endif

                    @can('view reports')
                    <a href="{{ route('reports.index') }}" data-spa class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-reports"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg></span>
                        <span class="sidebar-label">{{ __('reports.title') }}</span>
                    </a>
                    @endcan

                    @if(auth()->user()->can('manage users') || auth()->user()->can('manage settings') || auth()->user()->can('manage backups') || auth()->user()->can('view audit logs'))
                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.section_system') }}</p>
                    @can('manage users')
                    <a href="{{ route('users.index') }}" data-spa class="sidebar-link {{ request()->routeIs('users.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-users"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></span>
                        <span class="sidebar-label">{{ __('settings.users_nav_sidebar') }}</span>
                    </a>
                    @endcan
                    @can('view audit logs')
                    <a href="{{ route('audit-logs.index') }}" data-spa class="sidebar-link {{ request()->routeIs('audit-logs.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-audit"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.audit_logs') }}</span>
                    </a>
                    @endcan
                    @can('manage settings')
                    <a href="{{ route('settings.edit') }}" data-spa class="sidebar-link {{ request()->routeIs('settings.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-settings"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                        <span class="sidebar-label">{{ __('settings.nav_sidebar') }}</span>
                    </a>
                    @endcan
                    @can('manage backups')
                    <a href="{{ route('backups.index') }}" data-spa class="sidebar-link {{ request()->routeIs('backups.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-backups"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v.325c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-.325"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.backups') }}</span>
                    </a>
                    @endcan
                    @endif

                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('navigation.section_account') }}</p>
                    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'sidebar-link-active' : '' }}">
                        <span class="sidebar-icon-wrap sidebar-icon-tone-profile"><svg class="sidebar-svg" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></span>
                        <span class="sidebar-label">{{ __('navigation.profile') }}</span>
                    </a>
                </nav>
        </aside>

        <div id="locale-swap-root" class="app-shell-main">
            <div class="main-with-sidebar flex min-h-screen w-full min-w-0 flex-col">
                <nav id="app-navbar" aria-label="{{ __('navigation.navbar_aria_main') }}" class="app-topbar">
                    <div class="app-topbar-start">
                        <button type="button" id="mobile-menu-btn" class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#CBD5E1] dark:hover:bg-[#374151] lg:hidden" aria-label="{{ __('navigation.menu_mobile') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <span class="app-topbar-title">{{ __('ui.system_name') }}</span>
                    </div>

                    <div class="app-topbar-end">
                        <x-locale-globe />
                        <x-theme-toggle />
                        <x-header-notifications-dropdown
                            :notifications="$headerNotifications ?? []"
                            :unread-count="(int) ($unreadNotificationsCount ?? 0)"
                            :index-route="$headerNotificationsIndexRoute ?? null"
                        />
                        {{-- details/summary: يعمل بدون Alpine بعد تبديل اللغة أو استبدال DOM (أكثر موثوقية من x-show) --}}
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
                                <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-white dark:ring-[#374151]" width="36" height="36">
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
                                @role('admin')
                                @unless(auth()->user()->hasRole('super_admin'))
                                <a href="{{ route('saas.billing') }}" data-no-spa role="menuitem" onclick="this.closest('details')?.removeAttribute('open')" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 no-underline transition-colors hover:bg-slate-100 hover:text-[#0F4C81] dark:text-[#E5E7EB] dark:hover:bg-[#111827] dark:hover:text-[#93C5FD]">
                                    <svg class="h-5 w-5 shrink-0 text-[#0F4C81]/80" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M4 4h12v2H4V4zm0 4h12v8H4V8zm2 2v4h8v-4H6z"/></svg>
                                    {{ __('subscriptions.nav_billing') }}
                                </a>
                                @endunless
                                @endrole
                                <a href="{{ route('profile.edit') }}" data-no-spa role="menuitem" onclick="this.closest('details')?.removeAttribute('open')" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 no-underline transition-colors hover:bg-slate-100 hover:text-[#0F4C81] dark:text-[#E5E7EB] dark:hover:bg-[#111827] dark:hover:text-[#93C5FD]">
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
                </nav>

                <main id="app-content" class="relative z-0 flex-1 overflow-auto bg-[#F8F9FA] px-4 py-6 dark:bg-[#0F172A] sm:px-6 lg:px-8 lg:py-8">
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

        @auth
            @if(auth()->user()->clinic_id)
                @include('partials.internal-chat-widget')
            @endif
        @endauth

        <div id="spa-loader" class="spa-progress-bar pointer-events-none fixed start-0 top-0 z-[60] h-[2px] w-0 bg-[#0F4C81] opacity-0 shadow-none transition-[width,opacity] duration-200 ease-out dark:bg-[#60A5FA]" aria-hidden="true"></div>

        @stack('scripts')
    </body>
</html>
