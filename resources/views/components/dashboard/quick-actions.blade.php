@props(['actions' => []])
@if(count($actions) > 0)
<aside {{ $attributes->merge(['class' => 'dash-quick-actions']) }}>
    <h3 class="dash-quick-actions-title m-0">{{ __('dashboard.quick_actions_title') }}</h3>
    <div class="dash-quick-actions-grid">
        @foreach($actions as $action)
            @php
                $variant = $action['variant'] ?? 'ghost';
                $class = match ($variant) {
                    'primary' => 'dash-qa-btn dash-qa-btn--primary',
                    'amber' => 'dash-qa-btn dash-qa-btn--amber',
                    'violet' => 'dash-qa-btn dash-qa-btn--violet',
                    'orange' => 'dash-qa-btn dash-qa-btn--orange',
                    default => 'dash-qa-btn dash-qa-btn--ghost',
                };
            @endphp
            <a href="{{ $action['href'] }}" class="{{ $class }}" data-spa>{{ $action['label'] }}</a>
        @endforeach
    </div>
</aside>
@endif
