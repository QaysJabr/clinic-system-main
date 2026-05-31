@canany(['manage expenses', 'manage payroll', 'view reports'])
<x-dashboard.section
    id="dash-cash"
    :title="__('dashboard.section_cash_title')"
    :intro="__('dashboard.section_cash_intro')"
    compact
>
    <p class="mb-4 text-xs font-medium text-slate-500 dark:text-slate-400 m-0">{{ __('dashboard.section_cash_formula') }}</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.kpi
            :title="__('dashboard.cash_patient_in_title')"
            :value="number_format($totalPatientCashIn ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.cash_patient_in_hint')"
            :badge="__('dashboard.badge_cash')"
            accent="emerald"
            :tooltip="__('dashboard.cash_patient_in_tooltip')"
        />
        <x-dashboard.kpi
            :title="__('dashboard.cash_out_total_title')"
            :value="number_format($totalCashPaidOut ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.cash_out_total_hint')"
            :badge="__('dashboard.badge_cash')"
            accent="orange"
        />
        <x-dashboard.kpi
            :title="__('dashboard.cash_net_flow_title')"
            :value="number_format($netCashFlowLifetime ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.cash_net_flow_hint')"
            :badge="__('dashboard.badge_cash')"
            :accent="($netCashFlowLifetime ?? 0) < 0 ? 'red' : 'sky'"
            :tooltip="__('dashboard.cash_net_flow_tooltip')"
        />
        <x-dashboard.kpi
            :title="__('dashboard.cash_till_title')"
            :value="number_format($cashBalance ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.cash_till_hint')"
            :badge="__('dashboard.badge_cash')"
            accent="teal"
            :tooltip="__('dashboard.cash_till_tooltip')"
        />
    </div>
</x-dashboard.section>

@if(isset($accrualCashArDelta) && abs((float) $accrualCashArDelta) > 0.02)
    <p class="mb-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100 m-0">
        {{ __('dashboard.alert_accrual_reconcile', ['amount' => trim(number_format($accrualCashArDelta, 2).' '.(filled($cur ?? '') ? $cur : ''))]) }}
    </p>
@endif
@endcanany
