@props([
    'active' => 'clinic',
])

@php
    $tabs = [
        ['key' => 'clinic', 'route' => 'settings.edit', 'label' => __('settings.nav_clinic'), 'can' => 'manage settings'],
        ['key' => 'users', 'route' => 'users.index', 'label' => __('settings.nav_users'), 'can' => 'manage users'],
        ['key' => 'audit', 'route' => 'audit-logs.index', 'label' => __('settings.nav_audit'), 'can' => 'view audit logs'],
        ['key' => 'backups', 'route' => 'backups.index', 'label' => __('settings.nav_backups'), 'can' => 'manage backups'],
    ];
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('settings.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            @can($tab['can'])
                <li>
                    <a
                        href="{{ route($tab['route']) }}"
                        @if ($active === $tab['key']) aria-current="page" @endif
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                    >{{ $tab['label'] }}</a>
                </li>
            @endcan
        @endforeach
    </ul>
</nav>
