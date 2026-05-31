@props([
    'loadingText' => __('common.loading'),
])

{{-- لا نعطّل الزر عند submit — تعطيله يمنع إرسال النموذج في المتصفح ويبقي "جاري التحميل" للأبد --}}
<button
    type="submit"
    {{ $attributes->class([
        'auth-submit-btn inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-transparent sm:min-h-11',
        'bg-[#0F4C81] text-sm font-bold text-white shadow-lg shadow-blue-900/15',
        'transition duration-200 motion-safe:hover:-translate-y-0.5 motion-safe:hover:bg-[#0c3d66] motion-safe:hover:shadow-xl',
        'active:translate-y-0 active:shadow-md',
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0F4C81]/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-[#3B82F6]/50 dark:focus-visible:ring-offset-slate-900',
        'dark:bg-[#3B82F6] dark:hover:bg-blue-600',
    ]) }}
>
    <span class="auth-submit-btn__label inline-flex items-center gap-2">{{ $slot }}</span>
    <span class="auth-submit-btn__loading hidden inline-flex items-center gap-2" aria-hidden="true">
        <svg class="h-4 w-4 motion-safe:animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ $loadingText }}</span>
    </span>
</button>
