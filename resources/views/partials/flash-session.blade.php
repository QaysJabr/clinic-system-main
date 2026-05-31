@if (session('success'))
    <div class="mb-6" data-flash-auto-dismiss="5200" role="status">
        <div class="relative flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 py-3 ps-4 pe-11 text-emerald-800 dark:border-emerald-600 dark:bg-emerald-950/90 dark:text-emerald-100 sm:items-center sm:pe-12">
            <button type="button" data-flash-dismiss-btn class="absolute end-2 top-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-emerald-700 hover:bg-emerald-100/80 dark:text-emerald-200 dark:hover:bg-emerald-900/40 sm:top-1/2 sm:-translate-y-1/2" aria-label="{{ __('chat.close') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-300 sm:mt-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="m-0 min-w-0 flex-1 text-sm font-medium leading-relaxed">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="mb-6" data-flash-auto-dismiss="7000" role="alert">
        <div class="relative flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 py-3 ps-4 pe-11 text-red-800 dark:border-red-700 dark:bg-red-950/90 dark:text-red-100 sm:items-center sm:pe-12">
            <button type="button" data-flash-dismiss-btn class="absolute end-2 top-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-red-700 hover:bg-red-100/80 dark:text-red-200 dark:hover:bg-red-900/40 sm:top-1/2 sm:-translate-y-1/2" aria-label="{{ __('chat.close') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-300 sm:mt-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <p class="m-0 min-w-0 flex-1 text-sm font-medium leading-relaxed">{{ session('error') }}</p>
        </div>
    </div>
@endif
