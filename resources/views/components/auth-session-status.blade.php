@props(['status'])

@if ($status)
        <div data-flash-auto-dismiss="6500" {{ $attributes->merge(['class' => 'relative rounded-lg border border-[#2E8B57]/25 bg-[#E8F4EC] py-2 ps-3 pe-10 text-sm font-medium text-[#2E8B57] dark:border-emerald-600 dark:bg-emerald-950/90 dark:text-emerald-100']) }}>
        <button type="button" data-flash-dismiss-btn class="absolute end-1.5 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-md text-[#2E8B57] hover:bg-black/5 dark:text-emerald-200 dark:hover:bg-emerald-900/50 sm:h-7 sm:w-7" aria-label="{{ __('chat.close') }}">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <span class="block">{{ $status }}</span>
    </div>
@endif
