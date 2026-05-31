@props([
    'status',
])

@php
    $classes = match ($status) {
        'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300',
        'partial' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/45 dark:text-amber-300',
        default => 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300',
    };
    $label = match ($status) {
        'paid' => __('common.paid'),
        'partial' => __('common.partial'),
        default => __('common.unpaid'),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $label }}
</span>
