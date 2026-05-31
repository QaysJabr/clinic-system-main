@props([
    'id',
    'type' => 'line',
    'data' => [],
    'height' => '220',
    'empty' => null,
])
@php
    $hasData = ! empty($data);
    $emptyMessage = $empty ?? __('dashboard.chart_no_data');
    $heightClass = match ((int) $height) {
        260 => 'dash-chart-body--260',
        240 => 'dash-chart-body--240',
        default => 'dash-chart-body--220',
    };
@endphp
<div {{ $attributes->merge(['class' => 'dash-chart-card']) }}>
    @if(isset($header))
        <div class="dash-chart-card-header">{{ $header }}</div>
    @endif
    <div @class(['dash-chart-card-body', $heightClass])>
        @if($hasData)
            <canvas
                id="{{ $id }}"
                class="dash-chart-canvas h-full w-full"
                data-clinic-chart="{{ $type }}"
                data-chart-config='@json($data)'
                role="img"
                aria-label="{{ $attributes->get('aria-label', $id) }}"
            ></canvas>
        @else
            <x-dashboard.empty :message="$emptyMessage" />
        @endif
    </div>
</div>
