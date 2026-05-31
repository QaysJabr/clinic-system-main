<div class="mb-6 text-center lg:hidden">
    <a href="{{ url('/') }}" class="inline-flex flex-col items-center gap-3 no-underline" aria-label="{{ __('ui.home') }}">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#0F4C81] to-[#1F7A8C] text-white shadow-lg shadow-blue-900/15" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-9h6m-6 3h6m-6 3h6M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"/>
            </svg>
        </span>
        <span class="text-center">
            <span class="block truncate text-sm font-extrabold leading-snug text-slate-900 dark:text-white">{{ config('app.name', 'Clinic System') }}</span>
            <span class="mt-0.5 block text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ __('saas.landing_brand_subtitle') }}</span>
        </span>
    </a>
</div>
