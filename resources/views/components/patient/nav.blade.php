@props([
    'patient',
    'active' => 'emr',
])

@php
    $tabs = [
        ['key' => 'emr', 'route' => 'patients.show', 'label' => __('patients.nav_emr')],
        ['key' => 'profile', 'route' => 'patients.profile', 'label' => __('patients.nav_profile')],
        ['key' => 'statement', 'route' => 'patients.statement', 'label' => __('patients.nav_statement')],
    ];
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('patients.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route'], $patient) }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @can('update', $patient)
            <li>
                <a
                    href="{{ route('patients.edit', $patient) }}"
                    @if ($active === 'edit') aria-current="page" @endif
                    data-no-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'edit' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ __('patients.nav_edit') }}</a>
            </li>
        @endcan
    </ul>
</nav>
