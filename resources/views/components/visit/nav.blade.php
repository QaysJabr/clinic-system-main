@props([
    'active' => 'list',
    'visit' => null,
])

@php
    $tabs = [
        ['key' => 'list', 'route' => 'visits.index', 'params' => [], 'label' => __('visits.view_list')],
    ];
    if ($visit) {
        $tabs[] = ['key' => 'show', 'route' => 'visits.show', 'params' => [$visit], 'label' => __('visits.view_details')];
    }
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('visits.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route'], $tab['params']) }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @if ($visit)
            @can('update', $visit)
                <li>
                    <a
                        href="{{ route('visits.edit', $visit) }}"
                        @if ($active === 'edit') aria-current="page" @endif
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'edit' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                    >{{ __('visits.nav_edit') }}</a>
                </li>
            @endcan
        @else
            <li>
                <a
                    href="{{ route('visits.create', request()->only('patient_id', 'doctor_id', 'appointment_id')) }}"
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30"
                >{{ __('visits.add_new') }}</a>
            </li>
        @endif
    </ul>
</nav>
