@canany(['manage expenses', 'manage payroll', 'view reports'])
<x-dashboard.section
    id="dash-accrual"
    :title="__('dashboard.section_accrual_title')"
    :intro="__('dashboard.section_accrual_intro')"
>
    <x-slot:actions>
        <span class="dash-filter-pill">{{ __('dashboard.filter_period_all') }}</span>
    </x-slot:actions>

    <div class="mb-4 rounded-xl border border-amber-200/90 bg-amber-50/90 px-4 py-3 text-sm text-amber-950 dark:border-amber-900/45 dark:bg-amber-950/35 dark:text-amber-100" role="note">
        <span class="font-bold">{{ __('dashboard.section_accrual_note_label') }}</span>
        {{ __('dashboard.section_accrual_note_body') }}
    </div>
    <div class="mb-5 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-rose-100 bg-rose-50/60 px-3 py-2 text-sm dark:border-rose-900/35 dark:bg-rose-950/25">
        <span class="font-semibold text-rose-900 dark:text-rose-200">{{ __('dashboard.ar_title') }}</span>
        <span class="tabular-nums font-bold text-rose-800 dark:text-rose-300">{{ number_format($totalAccountsReceivable ?? 0, 2) }}@if(filled($cur ?? '')) <span class="text-xs font-medium">{{ $cur }}</span>@endif</span>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <x-dashboard.kpi
            :title="__('dashboard.card_total_revenue_title')"
            :value="number_format($totalAccrualRevenue ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.card_total_revenue_hint')"
            :badge="__('dashboard.badge_accrual')"
            accent="emerald"
            :tooltip="__('dashboard.card_total_revenue_tooltip')"
        />
        <x-dashboard.kpi
            :title="__('dashboard.card_doctor_share_title')"
            :value="number_format($totalDoctorShareExpense ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.card_doctor_share_hint')"
            :badge="__('dashboard.badge_accrual')"
            accent="cyan"
            :tooltip="__('dashboard.card_doctor_share_tooltip')"
        />
        <x-dashboard.kpi
            :title="__('dashboard.card_gross_profit_title')"
            :value="number_format($grossProfit ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.card_gross_profit_hint')"
            :badge="__('dashboard.badge_accrual')"
            accent="emerald"
            :tooltip="__('dashboard.card_gross_profit_tooltip')"
        />
        <x-dashboard.kpi
            :title="__('expenses.reports_dashboard_accrual_label')"
            :value="number_format($totalExpenses ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('expenses.reports_dashboard_accrual_hint')"
            :badge="__('dashboard.badge_accrual')"
            accent="amber"
        />
        <x-dashboard.kpi
            :title="__('payroll.reports_dashboard_accrual_label')"
            :value="number_format($totalPayrollPaid ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('payroll.reports_summary_payroll_accrual_short')"
            :badge="__('dashboard.badge_accrual')"
            accent="violet"
        />
        <x-dashboard.kpi
            :title="__('payroll.dashboard_accrual_cards_title')"
            :value="number_format($totalOperatingCost ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('payroll.reports_operating_cost_accrual_sub')"
            :badge="__('dashboard.badge_accrual')"
            accent="orange"
        />
        <x-dashboard.kpi
            :title="__('dashboard.card_net_profit_title')"
            :value="number_format($netProfit ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.card_net_profit_hint')"
            :badge="__('dashboard.badge_accrual')"
            :accent="($netProfit ?? 0) < 0 ? 'red' : 'brand'"
            :tooltip="__('dashboard.card_net_profit_tooltip')"
        />
    </div>
</x-dashboard.section>
@endcanany
