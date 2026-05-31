@props(['label', 'value', 'sub' => null, 'color' => 'brand'])
@php
    $valueClass = match ($color) {
        'green' => 'text-emerald-600 dark:text-emerald-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        'indigo' => 'text-indigo-600 dark:text-indigo-400',
        'teal' => 'text-teal-600 dark:text-teal-400',
        'brand' => 'text-[#0F4C81] dark:text-[#93C5FD]',
        default => 'text-slate-800 dark:text-slate-100',
    };
@endphp
<div {{ $attributes->merge(['class' => 'dash-stat-block']) }}>
    <p class="dash-stat-block-label m-0">{{ $label }}</p>
    <p class="dash-stat-block-value m-0 mt-2 tabular-nums {{ $valueClass }}">{{ $value }}</p>
    @if(filled($sub))
        <p class="dash-stat-block-sub m-0 mt-1">{{ $sub }}</p>
    @endif
</div>
