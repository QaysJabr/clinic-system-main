@props([
    'active' => 'financial',
    'doctor' => null,
])

@php
    $tabs = [
        ['key' => 'financial', 'route' => 'reports.index', 'label' => __('reports.nav_financial')],
        ['key' => 'receivables', 'route' => 'reports.receivables', 'label' => __('reports.nav_receivables_full')],
    ];
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('reports.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route']) }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @if ($showInventoryReports ?? false)
            <li>
                <a
                    href="{{ route('inventory.reports.index') }}"
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30"
                >{{ __('reports.nav_inventory') }}</a>
            </li>
        @endif
        @if ($doctor)
            <li>
                <a
                    href="{{ route('reports.doctors.show', array_merge(['doctor' => $doctor], request()->only(['date_from', 'date_to']))) }}"
                    @if ($active === 'doctor') aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'doctor' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-teal-700 hover:bg-teal-50 dark:text-teal-300 dark:hover:bg-teal-950/30' }}"
                >{{ $doctor->full_name }}</a>
            </li>
        @endif
    </ul>
</nav>
