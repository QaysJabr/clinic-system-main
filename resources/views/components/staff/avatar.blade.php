@props([
    'name',
    'size' => 'md',
])

@php
    $parts = preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = count($parts) >= 2
        ? mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1)
        : mb_substr(trim((string) $name), 0, 2);
    $sizeClass = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#0F4C81] to-violet-700 font-bold uppercase text-white shadow-sm ring-2 ring-white dark:ring-[#1F2937] {$sizeClass}"]) }} aria-hidden="true">
    {{ $initials }}
</span>
