@props([
    'accent' => 'brand',
    'title',
    'hint' => null,
])

@php
    $accents = [
        'brand' => [
            'card' => 'dash-metric-card--brand',
            'icon' => 'dash-metric-card-icon--brand',
            'value' => 'text-[#0F4C81] dark:text-[#93C5FD]',
            'iconPath' => 'M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h10v2H4v-2z',
        ],
        'violet' => [
            'card' => 'dash-metric-card--violet',
            'icon' => 'dash-metric-card-icon--violet',
            'value' => 'text-violet-800 dark:text-violet-300',
            'iconPath' => 'M7 2a2 2 0 00-2 2v1H5a2 2 0 00-2 2v11a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7zm0 2h6v1H7V4zm-2 3h10v11H5V7z',
        ],
        'indigo' => [
            'card' => 'dash-metric-card--indigo',
            'icon' => 'dash-metric-card-icon--indigo',
            'value' => 'text-indigo-800 dark:text-indigo-300',
            'iconPath' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z',
        ],
        'amber' => [
            'card' => 'dash-metric-card--amber',
            'icon' => 'dash-metric-card-icon--amber',
            'value' => 'text-amber-800 dark:text-amber-300',
            'iconPath' => 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z',
        ],
        'emerald' => [
            'card' => 'dash-metric-card--emerald',
            'icon' => 'dash-metric-card-icon--emerald',
            'value' => 'text-emerald-700 dark:text-emerald-400',
            'iconPath' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z',
        ],
        'rose' => [
            'card' => 'dash-metric-card--rose',
            'icon' => 'dash-metric-card-icon--rose',
            'value' => 'text-rose-700 dark:text-rose-300',
            'iconPath' => 'M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6h-6z',
        ],
        'teal' => [
            'card' => 'dash-metric-card--teal',
            'icon' => 'dash-metric-card-icon--teal',
            'value' => 'text-teal-800 dark:text-teal-300',
            'iconPath' => 'M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V10h16v8zm0-10H4V6h16v2z',
        ],
        'cyan' => [
            'card' => 'dash-metric-card--cyan',
            'icon' => 'dash-metric-card-icon--cyan',
            'value' => 'text-cyan-900 dark:text-cyan-300',
            'iconPath' => 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z',
        ],
        'sky' => [
            'card' => 'dash-metric-card--sky',
            'icon' => 'dash-metric-card-icon--sky',
            'value' => 'text-sky-800 dark:text-sky-300',
            'iconPath' => 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l7.59-7.59L21 8l-9 9z',
        ],
        'red' => [
            'card' => 'dash-metric-card--red',
            'icon' => 'dash-metric-card-icon--red',
            'value' => 'text-red-600 dark:text-red-400',
            'iconPath' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z',
        ],
    ];
    $a = $accents[$accent] ?? $accents['brand'];
@endphp

<div {{ $attributes->merge(['class' => 'dash-metric-card '.$a['card']]) }}>
    <span class="dash-metric-card-icon {{ $a['icon'] }}" aria-hidden="true">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $a['iconPath'] }}"/></svg>
    </span>
    <p class="dash-metric-card-label m-0">{{ $title }}</p>
    <div class="dash-metric-card-value m-0 mt-2 tabular-nums {{ $a['value'] }}">
        {{ $slot }}
    </div>
    @if(filled($hint))
        <p class="dash-metric-card-hint m-0 mt-1.5">{{ $hint }}</p>
    @endif
    @isset($footer)
        <div class="mt-2">{{ $footer }}</div>
    @endisset
</div>
