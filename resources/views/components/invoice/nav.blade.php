@props([
    'active' => 'list',
    'invoice' => null,
])

@php
    $tabs = [
        ['key' => 'list', 'route' => 'invoices.index', 'label' => __('invoices.nav_list')],
    ];
    if ($invoice) {
        $tabs[] = ['key' => 'show', 'route' => 'invoices.show', 'params' => [$invoice], 'label' => __('invoices.nav_details')];
    }
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('invoices.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route'], $tab['params'] ?? []) }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                    @if ($tab['key'] !== 'list') data-no-spa @else data-spa @endif
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @if ($invoice)
            @can('update', $invoice)
                <li>
                    <a
                        href="{{ route('invoices.edit', $invoice) }}"
                        @if ($active === 'edit') aria-current="page" @endif
                        data-no-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'edit' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                    >{{ __('invoices.nav_edit') }}</a>
                </li>
            @endcan
        @else
            @can('create', \App\Models\Invoice::class)
                <li>
                    <a
                        href="{{ route('invoices.create', request()->only('patient_id', 'visit_id')) }}"
                        @if ($active === 'create') aria-current="page" @endif
                        data-no-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'create' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-violet-700 hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30' }}"
                    >{{ __('invoices.add_new') }}</a>
                </li>
            @endcan
        @endif
    </ul>
</nav>
