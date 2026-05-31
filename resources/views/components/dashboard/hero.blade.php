@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'date' => null,
])
<header class="dash-hero mb-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            @if(filled($badge))
                <p class="dash-persona-badge m-0 mb-2">{{ $badge }}</p>
            @endif
            <h1 class="dash-hero-title m-0">{{ $title }}</h1>
            @if(filled($subtitle))
                <p class="dash-hero-sub m-0 mt-2">{{ $subtitle }}</p>
            @endif
            @if(filled($date))
                <p class="dash-hero-meta m-0 mt-1">{{ $date }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex flex-wrap gap-2 shrink-0">{{ $actions }}</div>
        @endif
    </div>
</header>
