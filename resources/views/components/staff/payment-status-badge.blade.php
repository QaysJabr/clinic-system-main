@props(['payment'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold '.$payment->statusBadgeClasses()]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $payment->statusDotColor() }}" aria-hidden="true"></span>
    {{ $payment->displayStatus() }}
</span>
