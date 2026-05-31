@props([
    'doctor',
    'active' => 'edit',
])

@php
    $tabs = [
        ['key' => 'edit', 'route' => 'doctors.edit', 'label' => __('doctors.nav_edit')],
        ['key' => 'schedules', 'route' => 'doctors.schedules.index', 'label' => __('doctors.nav_schedules')],
    ];
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('doctors.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route'], $doctor) }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                    @if ($tab['key'] === 'edit') data-no-spa @endif
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @can(\App\Support\ClinicPermissions::VIEW_REPORTS)
            <li>
                <a
                    href="{{ route('reports.doctors.show', $doctor) }}"
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-teal-700 transition hover:bg-teal-50 dark:text-teal-300 dark:hover:bg-teal-950/30"
                >{{ __('doctors.financial_report_link') }}</a>
            </li>
        @endcan
    </ul>
</nav>
