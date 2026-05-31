@php
    use App\Support\FormatBytes;
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $backupStats ?? ['count' => 0, 'total_bytes' => 0, 'latest' => null];
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif

<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('backups.page_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('backups.subtitle') }}</p>
            <p class="m-0 mt-2 text-xs text-slate-500 dark:text-slate-500">{{ __('backups.hint_db', ['driver' => $dbDriver ?? '', 'connection' => $dbConnection ?? '']) }}</p>
        </div>
        <form method="POST" action="{{ route('backups.store') }}" class="shrink-0" data-no-spa data-backup-create-form>
            @csrf
            <button
                type="submit"
                data-backup-submit
                class="inline-flex w-full items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] disabled:cursor-wait disabled:opacity-70 dark:bg-[#3B82F6] dark:hover:bg-blue-600 sm:w-auto"
            >
                <span data-backup-submit-label>{{ __('backups.btn_create') }}</span>
            </button>
        </form>
    </div>

    <x-settings.nav active="backups" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('backups.stat_count') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['count']) }}</p>
        </div>
        <div class="rounded-xl border border-teal-200/90 bg-teal-50/50 p-4 shadow-sm dark:border-teal-900/40 dark:bg-teal-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-teal-900 dark:text-teal-300">{{ __('backups.stat_total_size') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-teal-900 dark:text-teal-200">{{ FormatBytes::human($stats['total_bytes']) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200/90 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-indigo-900 dark:text-indigo-300">{{ __('backups.stat_latest') }}</p>
            <p class="m-0 mt-1 text-lg font-bold tabular-nums text-indigo-950 dark:text-indigo-100">
                @if ($stats['latest'] instanceof \Illuminate\Support\Carbon)
                    {{ $stats['latest']->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                @else
                    {{ __('backups.stat_latest_none') }}
                @endif
            </p>
        </div>
    </div>

    @if (config('backup.schedule_enabled', true))
        <div class="mb-4 rounded-xl border border-emerald-200/90 bg-emerald-50/70 px-4 py-3 text-sm font-semibold text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
            {{ __('backups.hint_schedule', ['time' => config('backup.schedule_time', '02:00'), 'days' => config('backup.retention_days', 14)]) }}
        </div>
    @endif
    <p class="mb-4 text-sm text-slate-600 dark:text-slate-400">{{ __('backups.hint_storage') }}</p>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('backups.section_files') }}</p>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-slate-700 dark:text-slate-200 sm:px-5">{{ __('backups.col_filename') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start font-bold text-slate-700 dark:text-slate-200 sm:px-5">{{ __('backups.col_size') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start font-bold text-slate-700 dark:text-slate-200 sm:px-5">{{ __('backups.col_modified') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-end font-bold text-slate-700 dark:text-slate-200 sm:px-5">{{ __('backups.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#374151]">
                        @forelse ($backups as $b)
                            <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-[#111827]/60">
                                <td class="px-4 py-3 font-mono text-xs font-medium text-slate-900 dark:text-slate-100 sm:px-5">{{ $b['name'] }}</td>
                                <td class="px-4 py-3 tabular-nums text-slate-700 dark:text-slate-200 sm:px-5">{{ FormatBytes::human($b['size']) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ $b['modified_at']->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a
                                            href="{{ route('backups.download', ['filename' => $b['name']]) }}"
                                            download="{{ $b['name'] }}"
                                            data-no-spa
                                            class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600"
                                        >
                                            {{ __('backups.btn_download') }}
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('backups.destroy', ['filename' => $b['name']]) }}"
                                            class="inline"
                                            data-confirm-title="{{ e(__('backups.confirm_delete_title')) }}"
                                            data-confirm="{{ e(__('backups.confirm_delete_body')) }}"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/50"
                                            >
                                                {{ __('backups.btn_delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('backups.empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('backups.empty_hint') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
