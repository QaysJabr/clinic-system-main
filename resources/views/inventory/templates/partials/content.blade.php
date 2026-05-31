@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $templateStats ?? ['total' => 0, 'auto_consume' => 0];
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.templates_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('inventory.template_lines_hint') }}</p>
        </div>
        @can('manage inventory')
            <a href="{{ route('inventory.templates.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md dark:bg-[#3B82F6]">{{ __('inventory.btn_add_template') }}</a>
        @endcan
    </div>

    <x-inventory.nav />

    <div class="mb-6 grid grid-cols-2 gap-3 sm:max-w-md">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('inventory.stat_templates_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('inventory.stat_templates_auto') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">{{ number_format($stats['auto_consume']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.list_heading_templates') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90">
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_template_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.th_lines') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_auto_consume') }}</th>
                            <th class="px-4 py-3 text-end font-bold">{{ __('inventory.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $t)
                            <tr class="border-b border-gray-50 hover:bg-slate-50/80 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $t->name }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $t->lines_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($t->auto_consume)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">{{ __('common.yes') }}</span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    @can('manage inventory')
                                        <a href="{{ route('inventory.templates.edit', $t) }}" data-spa class="text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('common.edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-14 text-center">
                                    <p class="m-0 font-semibold text-slate-600 dark:text-slate-300">{{ __('inventory.empty_templates_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500">{{ __('inventory.empty_templates') }}</p>
                                    @can('manage inventory')
                                        <a href="{{ route('inventory.templates.create') }}" data-spa class="mt-4 inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('inventory.btn_add_template') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($templates->hasPages())
                <div class="border-t px-4 py-3 dark:border-[#374151]">{{ $templates->links() }}</div>
            @endif
        </div>
    </div>
</div>
