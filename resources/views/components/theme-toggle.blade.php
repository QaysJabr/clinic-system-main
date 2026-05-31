@props([
    'class' => '',
])

<button
    type="button"
    data-theme-toggle
    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/25 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#9CA3AF] dark:hover:bg-[#111827] {{ $class }}"
    aria-label="{{ __('ui.theme_toggle_aria') }}"
    title="{{ __('ui.theme_toggle_title') }}"
>
    {{-- Light mode: show moon (switch to dark) --}}
    <svg class="h-[1.15rem] w-[1.15rem] dark:hidden" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
    </svg>
    {{-- Dark mode: show sun (switch to light) --}}
    <svg class="hidden h-[1.15rem] w-[1.15rem] dark:block" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
    </svg>
</button>
