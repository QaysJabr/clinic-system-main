@props([
    'title',
    'value',
    'hint' => null,
    'badge' => null,
    'accent' => 'brand',
    'tooltip' => null,
])
@php
    $accentMap = [
        'brand' => 'dash-kpi--brand',
        'emerald' => 'dash-kpi--emerald',
        'cyan' => 'dash-kpi--cyan',
        'amber' => 'dash-kpi--amber',
        'violet' => 'dash-kpi--violet',
        'rose' => 'dash-kpi--rose',
        'teal' => 'dash-kpi--teal',
        'sky' => 'dash-kpi--sky',
        'orange' => 'dash-kpi--orange',
        'red' => 'dash-kpi--red',
    ];
    $accentClass = $accentMap[$accent] ?? $accentMap['brand'];
@endphp
<div {{ $attributes->merge(['class' => 'dash-kpi '.$accentClass]) }} @if($tooltip) title="{{ $tooltip }}" @endif>
    <div class="dash-kpi-top">
        <p class="dash-kpi-label m-0">{{ $title }}</p>
        @if(filled($badge))
            <span class="dash-kpi-badge">{{ $badge }}</span>
        @endif
    </div>
    <p class="dash-kpi-value m-0 mt-2">{{ $value }}</p>
    @if(filled($hint))
        <p class="dash-kpi-hint m-0 mt-1.5">{{ $hint }}</p>
    @endif
</div>
