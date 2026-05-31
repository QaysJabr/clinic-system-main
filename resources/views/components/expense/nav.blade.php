@props([
    'active' => 'list',
    'expense' => null,
])

@php
    $tabs = [
        ['key' => 'list', 'route' => 'expenses.index', 'label' => __('expenses.nav_list')],
    ];
    if ($expense) {
        $tabs[] = ['key' => 'show', 'route' => 'expenses.show', 'params' => [$expense], 'label' => __('expenses.nav_details')];
    }
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('expenses.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route'], $tab['params'] ?? []) }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @if ($expense)
            <li>
                <a
                    href="{{ route('expenses.edit', $expense) }}"
                    @if ($active === 'edit') aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'edit' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ __('expenses.nav_edit') }}</a>
            </li>
        @else
            @can('manage expense categories')
                <li>
                    <a
                        href="{{ route('expense-categories.index') }}"
                        @if ($active === 'categories') aria-current="page" @endif
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $active === 'categories' ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                    >{{ __('expenses.nav_categories') }}</a>
                </li>
            @endcan
            @can('manage expenses')
                <li>
                    <a
                        href="{{ route('expenses.create') }}"
                        data-spa
                        class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30"
                    >{{ __('expenses.btn_add') }}</a>
                </li>
            @endcan
        @endif
    </ul>
</nav>
