@php
    $active = $active ?? '';
@endphp
<nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-[#374151]" aria-label="{{ __('platform.nav_aria') }}">
    <a href="{{ route('platform.dashboard') }}" data-spa @class([
        'inline-flex rounded-lg px-3 py-2 text-sm font-bold no-underline transition',
        'bg-[#0F4C81] text-white dark:bg-[#3B82F6]' => $active === 'dashboard',
        'text-slate-600 hover:bg-slate-100 dark:text-[#9CA3AF] dark:hover:bg-[#374151]' => $active !== 'dashboard',
    ])>{{ __('navigation.platform_dashboard') }}</a>
    <a href="{{ route('platform.clinics.index') }}" data-spa @class([
        'inline-flex rounded-lg px-3 py-2 text-sm font-bold no-underline transition',
        'bg-[#0F4C81] text-white dark:bg-[#3B82F6]' => $active === 'clinics',
        'text-slate-600 hover:bg-slate-100 dark:text-[#9CA3AF] dark:hover:bg-[#374151]' => $active !== 'clinics',
    ])>{{ __('navigation.sidebar_clinics') }}</a>
    <a href="{{ route('admin.plans.index') }}" data-spa @class([
        'inline-flex rounded-lg px-3 py-2 text-sm font-bold no-underline transition',
        'bg-[#0F4C81] text-white dark:bg-[#3B82F6]' => $active === 'plans',
        'text-slate-600 hover:bg-slate-100 dark:text-[#9CA3AF] dark:hover:bg-[#374151]' => $active !== 'plans',
    ])>{{ __('navigation.sidebar_subscription_plans') }}</a>
</nav>
