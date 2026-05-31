@props([
    'title',
    'intro' => null,
    'id' => null,
    'compact' => false,
])
<section
    @if($id) id="{{ $id }}" aria-labelledby="{{ $id }}-heading" @endif
    {{ $attributes->merge(['class' => 'dash-section mb-8'.($compact ? ' dash-section--compact' : '')]) }}
>
    <header class="dash-section-header">
        <div class="min-w-0 flex-1">
            <h2 @if($id) id="{{ $id }}-heading" @endif class="dash-section-title m-0">{{ $title }}</h2>
            @if(filled($intro))
                <p class="dash-section-intro m-0 mt-1.5">{{ $intro }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="shrink-0">{{ $actions }}</div>
        @endif
    </header>
    <div class="dash-section-body">
        {{ $slot }}
    </div>
</section>
