@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $persona = $dashboardPersona ?? 'admin';
    $isPersonal = (bool) ($dashboardStatsPersonal ?? false);
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif

<x-dashboard.shell dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    @include('dashboard.partials.header')

    <x-dashboard.alerts
        :alerts="$dashAlerts ?? []"
        :personal="$isPersonal"
        :currency="$cur ?? ''"
    />

    @include('dashboard.partials.onboarding-checklist')

    @include('dashboard.partials.pulse-today')

    @if(count($quickActions ?? []) > 0)
        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 m-0">{{ __('dashboard.quick_actions_title') }}</p>
        <div class="mb-8 flex flex-wrap gap-2">
            @foreach($quickActions as $action)
                @php
                    $variant = $action['variant'] ?? 'ghost';
                    $btnClass = match ($variant) {
                        'primary' => 'inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-[#0c3d6b] dark:bg-blue-600 dark:hover:bg-blue-500',
                        'amber' => 'inline-flex items-center justify-center rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold text-amber-900 hover:bg-amber-50 dark:border-amber-500/40 dark:bg-[#111827] dark:text-amber-200',
                        'violet' => 'inline-flex items-center justify-center rounded-xl border border-violet-300 bg-white px-4 py-2.5 text-sm font-bold text-violet-900 hover:bg-violet-50 dark:border-violet-500/40 dark:bg-[#111827] dark:text-violet-200',
                        'orange' => 'inline-flex items-center justify-center rounded-xl border border-orange-300 bg-white px-4 py-2.5 text-sm font-bold text-orange-900 hover:bg-orange-50 dark:border-orange-500/40 dark:bg-[#111827] dark:text-orange-200',
                        default => 'inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-[#374151] dark:bg-[#111827] dark:text-slate-200',
                    };
                @endphp
                <a href="{{ $action['href'] }}" class="{{ $btnClass }}" data-spa>{{ $action['label'] }}</a>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
        <div class="min-w-0 space-y-8 xl:col-span-8">
            @include('dashboard.partials.today-operations')

            @if(! empty($weeklyChart))
                @include('dashboard.partials.chart-week')
            @endif

            @if($showFinancialSnapshot ?? false)
                @include('dashboard.partials.financial-snapshot')
            @endif

            @if(in_array($persona, ['doctor'], true))
                @include('dashboard.partials.doctor-earnings-compact')
            @endif
        </div>

        <aside class="min-w-0 xl:col-span-4">
            @include('dashboard.partials.sidebar-recent')
        </aside>
    </div>
</x-dashboard.shell>
