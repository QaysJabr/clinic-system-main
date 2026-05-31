<x-dashboard.section
    :title="($dashboardStatsPersonal ?? false) ? __('dashboard.stats_heading_personal') : __('dashboard.stats_heading_all')"
    compact
>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 {{ ($dashboardStatsPersonal ?? false) ? 'lg:grid-cols-4' : 'lg:grid-cols-5' }}">
        <x-dashboard.stat-block
            :label="($dashboardStatsPersonal ?? false) ? __('dashboard.stats_patients_personal') : __('dashboard.stats_patients_all')"
            :value="$totalPatients ?? 0"
            :sub="($dashboardStatsPersonal ?? false) ? __('visits.dashboard_patients_with_visit_sub_own') : __('visits.dashboard_patients_with_visit_sub_all')"
        />
        @if(!($dashboardStatsPersonal ?? false))
            <x-dashboard.stat-block
                :label="__('dashboard.stats_doctors_title')"
                :value="$totalDoctors ?? 0"
                :sub="__('dashboard.stats_doctors_sub')"
                color="teal"
            />
        @endif
        <x-dashboard.stat-block
            :label="($dashboardStatsPersonal ?? false) ? __('appointments.dashboard_stat_title_own') : __('appointments.dashboard_stat_title_all')"
            :value="$totalAppointments ?? 0"
            :sub="($dashboardStatsPersonal ?? false) ? __('appointments.dashboard_stat_sub_own') : __('appointments.dashboard_stat_sub_all')"
            color="green"
        />
        <x-dashboard.stat-block
            :label="($dashboardStatsPersonal ?? false) ? __('visits.dashboard_visits_title_own') : __('visits.dashboard_visits_title_all')"
            :value="$totalVisits ?? 0"
            :sub="($dashboardStatsPersonal ?? false) ? __('visits.dashboard_visits_sub_own') : __('visits.dashboard_visits_sub_all')"
            color="indigo"
        />
        <x-dashboard.stat-block
            :label="($dashboardStatsPersonal ?? false) ? __('invoices.dashboard_stat_invoices_linked_visits_own') : __('invoices.dashboard_stat_invoices_total_all')"
            :value="$totalInvoices ?? 0"
            :sub="($dashboardStatsPersonal ?? false) ? __('invoices.dashboard_stat_invoices_sub_own') : __('invoices.dashboard_stat_invoices_sub_all')"
            color="amber"
        />
    </div>
</x-dashboard.section>

@can('manage invoices')
<x-dashboard.section
    :title="__('invoices.dashboard_section_activity')"
    compact
>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <x-dashboard.stat-block :label="__('invoices.dashboard_card_unpaid_title')" :value="$unpaidInvoices ?? 0" :sub="__('invoices.dashboard_card_unpaid_sub')" color="brand" />
        <x-dashboard.stat-block :label="__('invoices.dashboard_card_partial_title')" :value="$partialInvoices ?? 0" :sub="__('invoices.dashboard_card_partial_sub')" color="amber" />
        <x-dashboard.stat-block :label="__('invoices.dashboard_card_paid_title')" :value="$paidInvoices ?? 0" :sub="__('invoices.dashboard_card_paid_sub')" color="green" />
        <x-dashboard.stat-block :label="__('dashboard.activity_today_appts_title')" :value="$todayAppointments ?? 0" :sub="__('dashboard.activity_today_appts_sub')" color="teal" />
        <x-dashboard.stat-block :label="__('dashboard.activity_today_visits_title')" :value="$todayVisits ?? 0" :sub="__('dashboard.activity_today_visits_sub')" color="indigo" />
    </div>
</x-dashboard.section>
@endcan
