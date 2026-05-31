@php
    $weekly = $weeklyChart ?? [];
    $isVisits = isset($weekly['counts']);
    $labels = $weekly['labels'] ?? [];
    $values = $isVisits ? ($weekly['counts'] ?? []) : ($weekly['amounts'] ?? []);
    $hasData = array_sum($values) > 0;
    $chartColor = $isVisits ? 'rgba(124, 58, 237, 0.75)' : 'rgba(5, 150, 105, 0.75)';

    $chartConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [[
                'label' => $isVisits ? __('dashboard.chart_week_visits') : __('dashboard.chart_week_cash'),
                'data' => $values,
                'backgroundColor' => $chartColor,
                'borderRadius' => 8,
            ]],
        ],
    ];
@endphp
<div class="dash-panel-colored border-t-4 border-t-emerald-500 dark:border-t-emerald-500/75">
    <div class="border-b border-emerald-100 bg-gradient-to-r from-emerald-50/95 to-white px-4 py-3 dark:border-emerald-900/40 dark:from-emerald-950/40 dark:to-[#111827]">
        <h3 class="m-0 text-sm font-bold text-emerald-900 dark:text-emerald-200">{{ __('dashboard.chart_week_title') }}</h3>
        <p class="m-0 mt-1 text-xs text-emerald-800/80 dark:text-emerald-300/80">{{ __('dashboard.chart_week_sub') }}</p>
    </div>
    <div class="p-4">
        @if($hasData)
            <div class="h-[220px]">
                <canvas
                    id="chart-weekly-pulse"
                    class="h-full w-full"
                    data-clinic-chart="bar"
                    data-chart-config='@json($chartConfig)'
                    role="img"
                    aria-label="{{ __('dashboard.chart_week_aria') }}"
                ></canvas>
            </div>
        @else
            <p class="m-0 py-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.chart_no_data') }}</p>
        @endif
    </div>
</div>
