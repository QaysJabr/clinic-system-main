@canany(['view doctor earnings', 'manage doctor earnings'])
@isset($doctorEarning)
<x-dashboard.section-label bar="cyan">{{ __('dashboard.doctor_earn_section_title') }}</x-dashboard.section-label>
<div class="mb-3 grid grid-cols-1 gap-5 sm:grid-cols-3">
    <x-dashboard.metric-card accent="cyan" :title="__('dashboard.doctor_earn_total_title')" :hint="($dashboardStatsPersonal ?? false) ? __('dashboard.doctor_earn_total_hint_personal') : __('dashboard.doctor_earn_total_hint_all')">
        {{ number_format($doctorEarning['total'] ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
    </x-dashboard.metric-card>
    <x-dashboard.metric-card accent="amber" :title="__('dashboard.doctor_earn_pending_title')" :hint="__('dashboard.doctor_earn_pending_hint')">
        {{ number_format($doctorEarning['pending'] ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
    </x-dashboard.metric-card>
    <x-dashboard.metric-card accent="emerald" :title="__('dashboard.doctor_earn_paid_title')" :hint="__('dashboard.doctor_earn_paid_hint')">
        {{ number_format($doctorEarning['paid'] ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
    </x-dashboard.metric-card>
</div>
<p class="mb-0">
    <a href="{{ route('doctor-earnings.index') }}" class="text-sm font-bold text-[#0F4C81] hover:underline dark:text-blue-300" data-spa>{{ __('dashboard.doctor_earn_view_full') }} →</a>
</p>
@endisset
@endcanany
