@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div id="platform-clinics-page" class="max-w-6xl mx-auto" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}"
     data-expiring-days="{{ (int) config('platform.expiring_soon_days', 7) }}"
     data-url-index="{{ route('platform.clinics.index') }}"
>
    @include('platform.partials.nav', ['active' => 'clinics'])
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-[24px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('platform.clinics_title') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('platform.clinics_subtitle') }}</p>
        </div>
        <a href="{{ route('platform.clinics.export', request()->query()) }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-[#0F4C81]/30 bg-white px-4 py-2.5 text-sm font-bold text-[#0F4C81] shadow-sm no-underline transition hover:bg-[#0F4C81]/5 dark:border-[#3B82F6]/40 dark:bg-[#1F2937] dark:text-[#93C5FD] dark:hover:bg-blue-950/30">
            {{ __('platform.export_clinics_csv') }}
        </a>
    </div>

    <form id="platform-clinics-filters" class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-white p-3 dark:border-[#374151] dark:bg-[#1F2937] sm:grid-cols-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('platform.search_placeholder') }}" class="sm:col-span-2 rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
        <select name="is_active" class="rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
            <option value="">{{ __('platform.filter_all_activation') }}</option>
            <option value="1" @selected(request('is_active') === '1')>{{ __('common.active') }}</option>
            <option value="0" @selected(request('is_active') === '0')>{{ __('platform.status_suspended') }}</option>
        </select>
        <select name="subscription_status" class="rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
            <option value="">{{ __('platform.filter_all_subscription') }}</option>
            <option value="active" @selected(request('subscription_status') === 'active')>{{ __('platform.status_active') }}</option>
            <option value="expired" @selected(request('subscription_status') === 'expired')>{{ __('platform.status_expired') }}</option>
            <option value="expiring_soon" @selected(request('subscription_status') === 'expiring_soon')>{{ __('platform.filter_expiring_soon', ['days' => (int) config('platform.expiring_soon_days', 7)]) }}</option>
        </select>
    </form>

    <div id="platform-clinics-table-wrap">
        @include('platform.clinics.partials.table', ['clinics' => $clinics])
    </div>
</div>
