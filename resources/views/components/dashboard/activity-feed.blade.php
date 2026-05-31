@props(['items' => [], 'heading' => null])
<div {{ $attributes->merge(['class' => 'dash-activity-feed']) }}>
    <div class="dash-activity-feed-header">
        <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $heading ?? __('dashboard.activity_stream_title') }}</h3>
    </div>
    <ul class="dash-activity-list m-0 p-0 list-none">
        @forelse($items as $item)
            <li class="dash-activity-item">
                <span class="dash-activity-dot dash-activity-dot--{{ $item['type'] ?? 'default' }}" aria-hidden="true"></span>
                <div class="min-w-0 flex-1">
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}" class="dash-activity-title" data-spa>{{ $item['title'] }}</a>
                    @else
                        <p class="dash-activity-title m-0">{{ $item['title'] }}</p>
                    @endif
                    <p class="dash-activity-meta m-0 mt-0.5">{{ $item['meta'] ?? '' }}</p>
                </div>
            </li>
        @empty
            <li class="p-2">
                <x-dashboard.empty />
            </li>
        @endforelse
    </ul>
</div>
