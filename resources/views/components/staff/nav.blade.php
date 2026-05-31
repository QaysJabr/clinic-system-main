@props([
    'active' => 'list',
])

@php
    $tabs = [
        ['key' => 'list', 'route' => 'staff.index', 'label' => __('staff.nav_list'), 'can' => 'manage staff'],
        ['key' => 'compensation', 'route' => 'staff-compensation-profiles.index', 'label' => __('staff.nav_compensation'), 'can' => 'manage staff payroll'],
        ['key' => 'payments', 'route' => 'staff-payments.index', 'label' => __('staff.nav_payments'), 'can' => 'manage payroll'],
    ];
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('staff.nav_aria') }}">
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
        @can('manage staff')
            @if ($active === 'list')
                <li>
                    <a
                        href="{{ route('staff.create') }}"
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30"
                    >{{ __('staff.add_staff_btn') }}</a>
                </li>
            @endif
        @endcan
        @can('manage staff payroll')
            @if ($active === 'compensation')
                <li>
                    <a
                        href="{{ route('staff-compensation-profiles.create') }}"
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30"
                    >{{ __('staff.comp_profiles_add') }}</a>
                </li>
            @endif
        @endcan
        @can('manage payroll')
            @if ($active === 'payments')
                <li>
                    <a
                        href="{{ route('staff-payments.create') }}"
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30"
                    >{{ __('payroll.btn_add_record') }}</a>
                </li>
            @endif
        @endcan
    </ul>
</nav>
