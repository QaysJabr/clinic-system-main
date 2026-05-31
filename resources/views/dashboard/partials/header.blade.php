@php
    $clinicModel = $clinic ?? null;
@endphp
<div class="mb-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex flex-wrap items-start gap-4 min-w-0">
            @if($clinicModel?->logoPublicUrl())
                <img src="{{ $clinicModel->logoPublicUrl() }}" alt="" class="h-14 max-w-[160px] object-contain shrink-0">
            @endif
            <div class="min-w-0">
                @if(filled($dashboardPersonaLabel ?? null))
                    <p class="m-0 mb-1 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $dashboardPersonaLabel }}</p>
                @endif
                <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('dashboard.hero_title_ops') }}</h1>
                <p class="m-0 mt-1 text-sm font-semibold text-[#0F4C81] dark:text-blue-300">{{ $clinicModel?->clinic_name ?: config('app.name') }}</p>
                <p class="m-0 mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ ($dashboardStatsPersonal ?? false) ? __('dashboard.hero_subtitle_personal') : __('dashboard.hero_subtitle_ops') }}
                </p>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.last_updated_at') }} {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @can('view reports')
                <a href="{{ route('reports.index') }}" data-spa class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-[#0F4C81]/20 hover:bg-[#0c3d6b] dark:bg-blue-600 dark:hover:bg-blue-500">
                    {{ __('dashboard.cta_full_reports') }}
                </a>
                <a href="{{ route('reports.receivables') }}" data-spa class="inline-flex items-center justify-center rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-bold text-rose-700 shadow-sm hover:bg-rose-50 dark:border-rose-500/40 dark:bg-[#111827] dark:text-rose-300 dark:hover:bg-rose-950/30">
                    {{ __('reports.nav_receivables') }}
                </a>
            @endcan
            @can('manage appointments')
                <a href="{{ route('appointments.index') }}" data-spa class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-[#374151] dark:bg-[#111827] dark:text-slate-200">
                    {{ __('appointments.nav_appointments') }}
                </a>
            @endcan
        </div>
    </div>
</div>
