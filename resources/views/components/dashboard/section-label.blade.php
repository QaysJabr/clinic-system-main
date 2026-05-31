@props(['bar' => 'brand'])
@php
    $barClass = match ($bar) {
        'violet' => 'bg-violet-500',
        'emerald' => 'bg-emerald-500',
        'rose' => 'bg-rose-500',
        'cyan' => 'bg-cyan-500',
        default => 'bg-[#0F4C81] dark:bg-blue-500',
    };
@endphp
<p {{ $attributes->merge(['class' => 'dash-section-label m-0']) }}>
    <span class="dash-section-label-bar {{ $barClass }}"></span>
    <span>{{ $slot }}</span>
</p>
