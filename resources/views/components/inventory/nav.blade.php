@php
    $tabs = [
        ['key' => 'dashboard', 'route' => 'inventory.dashboard', 'match' => 'inventory.dashboard', 'label' => __('inventory.nav_overview')],
        ['key' => 'items', 'route' => 'inventory.items.index', 'match' => 'inventory.items.*', 'label' => __('inventory.nav_items')],
        ['key' => 'movements', 'route' => 'inventory.movements.index', 'match' => 'inventory.movements.*', 'label' => __('inventory.nav_movements')],
        ['key' => 'purchases', 'route' => 'inventory.purchases.index', 'match' => 'inventory.purchases.*', 'label' => __('inventory.nav_purchases')],
        ['key' => 'suppliers', 'route' => 'inventory.suppliers.index', 'match' => 'inventory.suppliers.*', 'label' => __('inventory.nav_suppliers')],
        ['key' => 'templates', 'route' => 'inventory.templates.index', 'match' => 'inventory.templates.*', 'label' => __('inventory.nav_templates')],
        ['key' => 'reports', 'route' => 'inventory.reports.index', 'match' => 'inventory.reports.*', 'label' => __('inventory.nav_reports')],
    ];
    $activeKey = 'dashboard';
    foreach ($tabs as $tab) {
        if (request()->routeIs($tab['match'])) {
            $activeKey = $tab['key'];
            break;
        }
    }
    $cta = match ($activeKey) {
        'items' => ['route' => 'inventory.items.create', 'label' => __('inventory.btn_add_item'), 'class' => 'text-violet-700 hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30'],
        'movements' => ['route' => 'inventory.movements.create', 'label' => __('inventory.btn_record_movement'), 'class' => 'text-teal-700 hover:bg-teal-50 dark:text-teal-300 dark:hover:bg-teal-950/30'],
        'purchases' => ['route' => 'inventory.purchases.create', 'label' => __('inventory.btn_receive_stock'), 'class' => 'text-teal-700 hover:bg-teal-50 dark:text-teal-300 dark:hover:bg-teal-950/30'],
        'templates' => ['route' => 'inventory.templates.create', 'label' => __('inventory.btn_add_template'), 'class' => 'text-violet-700 hover:bg-violet-50 dark:text-violet-300 dark:hover:bg-violet-950/30'],
        default => null,
    };
@endphp

<nav class="mb-6 overflow-x-auto rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]" aria-label="{{ __('inventory.nav_aria') }}">
    <ul class="m-0 flex min-w-max list-none gap-1 p-0">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route($tab['route']) }}"
                    @if ($activeKey === $tab['key']) aria-current="page" @endif
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $activeKey === $tab['key'] ? 'bg-[#0F4C81] text-white shadow-sm dark:bg-[#3B82F6]' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#111827]' }}"
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
        @if ($cta && auth()->user()->can('manage inventory'))
            <li>
                <a
                    href="{{ route($cta['route']) }}"
                    data-spa
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $cta['class'] }}"
                >{{ $cta['label'] }}</a>
            </li>
        @endif
    </ul>
</nav>
