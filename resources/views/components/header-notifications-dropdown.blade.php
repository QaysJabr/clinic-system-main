@props([
    'notifications' => [],
    'unreadCount' => 0,
    'indexRoute' => null,
    'readAllRoute' => null,
])

@php
    $showBell = filled($indexRoute);
@endphp

@if($showBell)
    @php
        $showBadge = (int) $unreadCount > 0;
        $badgeLabel = (int) $unreadCount > 99 ? '99+' : (string) (int) $unreadCount;
        $bellTypeLabels = \App\Support\AppNotificationType::labels();
        $bellTypePillClass = \App\Support\AppNotificationType::pillClasses();
        $bellTypePillDefault = 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200';
    @endphp
    <details data-header-popover data-header-notifications class="header-notifications relative z-50">
        <summary
            data-header-notifications-trigger
            class="relative flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#CBD5E1] dark:hover:bg-[#374151] [&::-webkit-details-marker]:hidden"
            aria-label="{{ __('navigation.notifications') }}"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
            @if($showBadge)
                <span class="absolute -top-0.5 -end-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white">{{ $badgeLabel }}</span>
            @endif
        </summary>
        <div
            data-header-notifications-panel
            class="header-notifications-panel overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/15 dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-black/40"
            role="region"
            aria-label="{{ __('navigation.notifications_recent') }}"
        >
            <p class="shrink-0 border-b border-slate-100 px-3 py-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:border-[#374151] dark:text-slate-500">{{ __('navigation.notifications_recent') }}</p>
            <div class="header-notifications-panel__list min-h-0 flex-1 overflow-y-auto overscroll-contain">
                @forelse($notifications as $hn)
                    <a href="{{ $indexRoute }}" data-no-spa class="block border-b border-slate-50 px-3 py-2.5 text-start transition hover:bg-slate-50 dark:border-[#374151] dark:hover:bg-[#111827] {{ !($hn->is_read ?? true) ? 'bg-blue-50/50 dark:bg-[#1e3a5f]/25' : '' }}">
                        <span class="mb-1 inline-flex max-w-full flex-wrap items-center gap-1.5">
                            <span class="inline-flex shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold leading-none {{ $bellTypePillClass[$hn->type] ?? $bellTypePillDefault }}">{{ $bellTypeLabels[$hn->type] ?? $hn->type }}</span>
                            @if(!($hn->is_read ?? true))
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#0F4C81] dark:bg-[#60A5FA]" title="{{ __('navigation.notifications_unread') }}" aria-hidden="true"></span>
                            @endif
                        </span>
                        <span class="line-clamp-1 text-sm font-semibold text-slate-800 dark:text-[#F3F4F6]">{{ $hn->title }}</span>
                        <span class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-[#9CA3AF]">{{ $hn->message }}</span>
                        <span class="mt-1 block text-[11px] text-slate-400 dark:text-slate-500">{{ $hn->created_at->format('d/m/Y H:i') }}</span>
                    </a>
                @empty
                    <p class="px-3 py-6 text-center text-sm text-slate-500 dark:text-[#9CA3AF]">{{ __('navigation.notifications_none') }}</p>
                @endforelse
            </div>
            <div class="shrink-0 border-t border-slate-100 bg-white px-2 py-2 dark:border-[#374151] dark:bg-[#1F2937]">
                <a href="{{ $indexRoute }}" data-no-spa class="block rounded-lg bg-[#0F4C81] px-3 py-2 text-center text-sm font-semibold text-white no-underline hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('navigation.notifications_view_all') }}</a>
            </div>
        </div>
    </details>
@endif
