@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $sessionStats ?? ['total' => 0, 'others' => 0];
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('security.sessions_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('security.sessions_intro') }}</p>
        </div>
        @if ($stats['others'] > 0)
            <form method="POST" action="{{ route('security.sessions.revoke-others') }}" class="shrink-0">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-white px-5 py-2.5 text-sm font-bold text-red-700 shadow-sm transition hover:bg-red-50 dark:border-red-900/40 dark:bg-[#1F2937] dark:text-red-300 dark:hover:bg-red-950/30 sm:w-auto">
                    {{ __('security.session_revoke_others') }}
                </button>
            </form>
        @endif
    </div>

    <x-profile.nav active="sessions" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('profile.sessions_stat_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-900 dark:text-emerald-300">{{ __('profile.sessions_stat_current') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-950 dark:text-emerald-100">1</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">{{ __('profile.sessions_stat_others') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-950 dark:text-amber-200">{{ number_format($stats['others']) }}</p>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('profile.sessions_list_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            @forelse ($sessions as $session)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 last:border-b-0 dark:border-[#374151] sm:px-5">
                    <div class="min-w-0">
                        <p class="m-0 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $session->device_name }}</p>
                        <p class="m-0 mt-1 text-xs text-gray-500 dark:text-[#9CA3AF]">{{ $session->ip_address }} · {{ $session->last_active_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                        @if ($session->is_current)
                            <span class="mt-2 inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300">{{ __('security.session_current') }}</span>
                        @endif
                    </div>
                    @unless ($session->is_current)
                        <form method="POST" action="{{ route('security.sessions.destroy', $session->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 dark:text-red-400">{{ __('security.session_revoke') }}</button>
                        </form>
                    @endunless
                </div>
            @empty
                <p class="m-0 px-4 py-10 text-center text-sm text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('common.no_results') }}</p>
            @endforelse
        </div>
    </div>
</div>
