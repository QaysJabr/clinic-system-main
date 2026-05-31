@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
        $notificationsReadAllRoute = $notificationsReadAllRoute ?? 'notifications.readAll';
        $notificationsReadRoute = $notificationsReadRoute ?? 'notifications.read';
        $notificationsTitle = $notificationsTitle ?? __('notifications.title');
        $notificationsSubtitle = $notificationsSubtitle ?? __('notifications.subtitle_clinic');
        $typeLabels = \App\Support\AppNotificationType::labels();
        $typePillClass = \App\Support\AppNotificationType::pillClasses();
        $typePillDefault = 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200';
    @endphp
    @php $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true); @endphp
    <div class="max-w-[1400px] mx-auto" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ $notificationsTitle }}</h1>
                <p class="text-gray-600 dark:text-[#9CA3AF] text-sm mt-2 m-0">{{ $notificationsSubtitle }}</p>
            </div>
            @if($notifications->isNotEmpty())
                <form method="POST" action="{{ route($notificationsReadAllRoute) }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#F3F4F6] dark:hover:bg-[#374151]">
                        {{ __('notifications.mark_all_read') }}
                    </button>
                </form>
            @endif
        </div>

        <p class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide mb-3 m-0">{{ __('notifications.list_kicker') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90 px-4 py-4 sm:px-5">
                <h2 class="text-base font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('notifications.latest_title') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('notifications.col_type') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('notifications.col_title') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 min-w-[220px]">{{ __('notifications.col_message') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">{{ __('notifications.col_status') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">{{ __('notifications.col_date') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">{{ __('notifications.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $n)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:hover:bg-[#111827]/80 {{ $n->is_read ? '' : 'bg-blue-50/40' }}">
                                <td class="px-4 py-3 text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typePillClass[$n->type] ?? $typePillDefault }}">{{ $typeLabels[$n->type] ?? $n->type }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900 sm:px-5">{{ $n->title }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5 max-w-md">{{ \Illuminate\Support\Str::limit($n->message, 160) }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if($n->is_read)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">{{ __('notifications.status_read') }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-[#0F4C81]/10 px-2.5 py-0.5 text-xs font-semibold text-[#0F4C81]">{{ __('notifications.status_unread') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5 whitespace-nowrap">{{ $n->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 sm:px-5 whitespace-nowrap">
                                    @if(! $n->is_read)
                                        <form method="POST" action="{{ route($notificationsReadRoute, $n) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('notifications.mark_read') }}</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-[#94A3B8]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('notifications.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notifications->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 sm:px-5">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
