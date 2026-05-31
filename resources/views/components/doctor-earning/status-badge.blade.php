@props(['earning'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold '.$earning->statusBadgeClasses()]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $earning->statusDotColor() }}" aria-hidden="true"></span>
    {{ $earning->statusLabel() }}
</span>
