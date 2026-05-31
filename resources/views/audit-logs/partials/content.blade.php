@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $auditStats ?? ['matching' => 0, 'exports' => 0, 'destructive' => 0];
    $hasFilters = request()->filled('user_id') || request()->filled('module') || request()->filled('action') || request()->filled('date');
    $todayParam = now()->toDateString();
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('audit.page_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('audit.subtitle') }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('audit-logs.index', array_merge(request()->except('date'), ['date' => $todayParam])) }}" data-spa class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300 dark:hover:bg-[#111827]">{{ __('audit.quick_today') }}</a>
            <a href="{{ route('audit-logs.index') }}" data-spa class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300 dark:hover:bg-[#111827]">{{ __('audit.quick_all_dates') }}</a>
        </div>
    </div>

    <x-settings.nav active="audit" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('audit.stat_matching') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['matching']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">{{ __('audit.stat_exports') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-950 dark:text-amber-200">{{ number_format($stats['exports']) }}</p>
        </div>
        <div class="rounded-xl border border-rose-200/90 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-rose-900 dark:text-rose-300">{{ __('audit.stat_deletes') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-rose-900 dark:text-rose-200">{{ number_format($stats['destructive']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('audit.section_filter') }}</p>
        <form method="GET" action="{{ route('audit-logs.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('audit.filter_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-3">
                        <label for="user_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('audit.label_user') }}</label>
                        <select name="user_id" id="user_id"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('audit.filter_all') }}</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="module" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('audit.label_module') }}</label>
                        <select name="module" id="module"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('audit.filter_all') }}</option>
                            @foreach ($modules as $key => $label)
                                <option value="{{ $key }}" @selected(request('module') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="action" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('audit.label_action') }}</label>
                        <select name="action" id="action"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('audit.filter_all') }}</option>
                            @foreach ($actions as $key => $label)
                                <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('audit.label_date') }}</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-3 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('audit.btn_search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('audit-logs.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('audit.btn_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="dash-section-label m-0">{{ __('audit.section_log_list') }}</p>
            @if ($logs->total() > 0)
                <p class="m-0 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('audit.results_count', ['from' => $logs->firstItem(), 'to' => $logs->lastItem(), 'total' => $logs->total()]) }}
                </p>
            @endif
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('audit.col_user') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('audit.col_action') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('audit.col_module') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('audit.col_record_id') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 min-w-[200px]">{{ __('audit.col_description') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('audit.col_ip') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('audit.col_datetime') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 text-gray-900 dark:text-[#E5E7EB] sm:px-5">
                                    @if ($log->user)
                                        <span class="font-semibold">{{ $log->user->name }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $log->user->email }}</span>
                                    @else
                                        <span class="text-gray-400 dark:text-[#94A3B8]">{{ __('audit.user_system') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <x-audit.action-badge
                                        :action="$log->action"
                                        :label="$actions[$log->action] ?? $log->action"
                                    />
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200">{{ $modules[$log->module] ?? $log->module }}</span>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ $log->record_id ?? __('common.em_dash') }}</td>
                                <td class="max-w-md px-4 py-3 text-gray-700 dark:text-[#D1D5DB] sm:px-5">{{ \Illuminate\Support\Str::limit((string) ($log->description ?? ''), 140) ?: __('common.em_dash') }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-[#CBD5E1] sm:px-5">{{ $log->ip_address ?? __('common.em_dash') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700 dark:text-[#CBD5E1] sm:px-5">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-gray-800 dark:text-[#F3F4F6]">{{ __('audit.empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-gray-500 dark:text-[#9CA3AF]">{{ $hasFilters ? __('audit.empty_filtered') : __('audit.empty') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-gray-100 bg-[#FAFBFC] px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/40 sm:px-5">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
