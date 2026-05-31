@props(['message' => null, 'hint' => null, 'icon' => true, 'ctaHref' => null, 'ctaLabel' => null, 'ctaNoSpa' => false])
<div {{ $attributes->merge(['class' => 'dash-empty']) }}>
    @if($icon)
        <div class="dash-empty-icon" aria-hidden="true">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
        </div>
    @endif
    <p class="dash-empty-text m-0 font-semibold text-slate-700 dark:text-slate-200">{{ $message ?? __('dashboard.no_data') }}</p>
    @if(filled($hint))
        <p class="m-0 max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    @if(filled($ctaHref) && filled($ctaLabel))
        <a href="{{ $ctaHref }}" @if($ctaNoSpa) data-no-spa @else data-spa @endif class="mt-2 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ $ctaLabel }}</a>
    @endif
</div>
