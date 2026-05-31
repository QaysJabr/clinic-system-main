@props([
    'action',
    'label',
])

@php
    $name = (string) $action;
    $classes = match ($name) {
        'create' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/45 dark:text-emerald-300',
        'update' => 'bg-sky-100 text-sky-900 dark:bg-sky-950/45 dark:text-sky-300',
        'delete' => 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300',
        'payment', 'staff_payment' => 'bg-violet-100 text-violet-900 dark:bg-violet-950/45 dark:text-violet-300',
        'login', 'logout' => 'bg-slate-200 text-slate-900 dark:bg-slate-700 dark:text-slate-100',
        'export', 'download' => 'bg-amber-100 text-amber-950 dark:bg-amber-950/35 dark:text-amber-200',
        default => 'bg-slate-100 text-slate-800 dark:bg-slate-700/70 dark:text-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {$classes}"]) }}>
    {{ $label }}
</span>
