@php
    $openCount = (int) ($openInvoicesCount ?? 0);
    $curSuffix = filled($cur ?? '') ? ' '.$cur : '';
    $invoiceHint = ($showFinancialSnapshot ?? false)
        ? __('dashboard.pulse_invoices_ar', ['amount' => number_format($totalAccountsReceivable ?? 0, 2).$curSuffix])
        : __('dashboard.pulse_invoices_hint');
@endphp
<div class="dash-section-group">
    <x-dashboard.section-label bar="violet">{{ __('dashboard.section_pulse') }}</x-dashboard.section-label>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-dashboard.metric-card accent="violet" :title="__('dashboard.pulse_appointments')" :hint="__('dashboard.pulse_appointments_hint')">
            {{ number_format($todayAppointments ?? 0) }}
        </x-dashboard.metric-card>

        <x-dashboard.metric-card accent="indigo" :title="__('dashboard.pulse_visits')" :hint="__('dashboard.pulse_visits_waiting', ['count' => number_format($todayVisitsWaiting ?? 0)])">
            {{ number_format($todayVisits ?? 0) }}
        </x-dashboard.metric-card>

        @can('manage invoices')
            <x-dashboard.metric-card accent="amber" :title="__('dashboard.pulse_invoices')" :hint="$invoiceHint">
                {{ number_format($openCount) }}
            </x-dashboard.metric-card>
        @endcan

        @if(($showFinancialSnapshot ?? false) || auth()->user()?->can('manage payments'))
            <x-dashboard.metric-card accent="emerald" :title="__('dashboard.pulse_cash_today')" :hint="__('dashboard.pulse_cash_today_hint')">
                {{ number_format($todayCashCollected ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-base font-semibold text-slate-500 dark:text-slate-400">{{ $cur }}</span>@endif
            </x-dashboard.metric-card>
        @endif
    </div>
</div>
