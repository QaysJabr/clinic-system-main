@props([
    'status',
])

@php
    $classes = match ($status) {
        'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300',
        'partial' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/45 dark:text-amber-300',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    };
    $label = match ($status) {
        'paid' => __('expenses.status_full_settled'),
        'partial' => __('expenses.status_partial_settled'),
        default => __('expenses.status_unpaid'),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $label }}
</span>
