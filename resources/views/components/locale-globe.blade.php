@props([
    'summaryClass' => 'flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 [&::-webkit-details-marker]:hidden',
])
@php
    $currentLocale = app()->getLocale();
@endphp
<details data-header-popover data-locale-menu data-apply-url="{{ route('locale.apply') }}" class="group relative">
    <summary class="{{ $summaryClass }}" aria-label="{{ __('common.language') }}" title="{{ __('common.language') }}">
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
        </svg>
    </summary>
    <div class="absolute end-0 top-full z-50 mt-1 min-w-[11rem] overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg shadow-slate-900/10 dark:border-slate-600 dark:bg-slate-800 dark:shadow-black/40" role="menu">
        <button type="button" data-locale="ar" class="flex w-full items-center gap-2 px-3 py-2.5 text-start text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:text-slate-100 dark:hover:bg-slate-700 {{ $currentLocale === 'ar' ? 'bg-slate-100 dark:bg-slate-700/80' : '' }}">
            @if ($currentLocale === 'ar')
                <span class="text-teal-700 dark:text-teal-400" aria-hidden="true">✓</span>
            @else
                <span class="w-4 shrink-0" aria-hidden="true"></span>
            @endif
            {{ __('common.arabic') }}
        </button>
        <button type="button" data-locale="en" class="flex w-full items-center gap-2 px-3 py-2.5 text-start text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:text-slate-100 dark:hover:bg-slate-700 {{ $currentLocale === 'en' ? 'bg-slate-100 dark:bg-slate-700/80' : '' }}">
            @if ($currentLocale === 'en')
                <span class="text-teal-700 dark:text-teal-400" aria-hidden="true">✓</span>
            @else
                <span class="w-4 shrink-0" aria-hidden="true"></span>
            @endif
            {{ __('common.english') }}
        </button>
    </div>
</details>
