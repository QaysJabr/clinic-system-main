@php
    $charts = $charts ?? [];
    $persona = $dashboardPersona ?? 'admin';
    $showFinancialCharts = in_array($persona, ['admin', 'accountant'], true) || auth()->user()?->can('view reports');
    $showDoctorChart = ($dashboardStatsPersonal ?? false) === false && !empty($charts['doctors']['labels'] ?? []);

    $revenueConfig = [
        'type' => 'line',
        'data' => [
            'labels' => $charts['revenue']['labels'] ?? [],
            'datasets' => [
                [
                    'label' => __('dashboard.chart_accrual'),
                    'data' => $charts['revenue']['accrual'] ?? [],
                    'fill' => true,
                    'borderColor' => '#0F4C81',
                    'backgroundColor' => 'rgba(15, 76, 129, 0.12)',
                ],
                [
                    'label' => __('dashboard.chart_cash'),
                    'data' => $charts['revenue']['cash'] ?? [],
                    'fill' => true,
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.12)',
                ],
            ],
        ],
    ];

    $appointmentsConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $charts['appointments']['labels'] ?? [],
            'datasets' => [[
                'label' => __('dashboard.chart_appointments'),
                'data' => $charts['appointments']['counts'] ?? [],
                'backgroundColor' => 'rgba(124, 58, 237, 0.65)',
                'borderRadius' => 6,
            ]],
        ],
    ];

    $patientsConfig = [
        'type' => 'line',
        'data' => [
            'labels' => $charts['patients']['labels'] ?? [],
            'datasets' => [[
                'label' => ($dashboardStatsPersonal ?? false) ? __('dashboard.chart_patients_seen') : __('dashboard.chart_patients_new'),
                'data' => $charts['patients']['counts'] ?? [],
                'fill' => true,
                'borderColor' => '#0891B2',
                'backgroundColor' => 'rgba(8, 145, 178, 0.12)',
            ]],
        ],
    ];

    $paymentsConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $charts['payments']['labels'] ?? [],
            'datasets' => [[
                'label' => __('dashboard.chart_payments'),
                'data' => $charts['payments']['amounts'] ?? [],
                'backgroundColor' => 'rgba(217, 119, 6, 0.7)',
                'borderRadius' => 6,
            ]],
        ],
    ];

    $invoiceStatusConfig = [
        'type' => 'doughnut',
        'data' => [
            'labels' => [
                __('invoices.dashboard_card_unpaid_title'),
                __('invoices.dashboard_card_partial_title'),
                __('invoices.dashboard_card_paid_title'),
            ],
            'datasets' => [[
                'data' => [
                    (int) ($unpaidInvoices ?? 0),
                    (int) ($partialInvoices ?? 0),
                    (int) ($paidInvoices ?? 0),
                ],
                'backgroundColor' => ['#EF4444', '#F59E0B', '#10B981'],
                'borderWidth' => 0,
            ]],
        ],
    ];

    $doctorsConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $charts['doctors']['labels'] ?? [],
            'datasets' => [[
                'label' => __('dashboard.chart_doctor_visits'),
                'data' => $charts['doctors']['visits'] ?? [],
                'backgroundColor' => 'rgba(15, 118, 110, 0.75)',
                'borderRadius' => 6,
            ]],
        ],
        'options' => ['indexAxis' => 'y'],
    ];
@endphp

<x-dashboard.section
    id="dash-analytics"
    :title="__('dashboard.analytics_section_title')"
    :intro="__('dashboard.analytics_section_intro')"
>
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        @if($showFinancialCharts)
            <div class="xl:col-span-8">
                <x-dashboard.chart
                    id="chart-revenue"
                    type="line"
                    :data="$revenueConfig"
                    height="260"
                    :aria-label="__('dashboard.chart_revenue_aria')"
                >
                    <x-slot:header>
                        <h3 class="dash-chart-title m-0">{{ __('dashboard.chart_revenue_title') }}</h3>
                        <p class="dash-chart-sub m-0 mt-1">{{ __('dashboard.chart_revenue_sub') }}</p>
                    </x-slot:header>
                </x-dashboard.chart>
            </div>
            <div class="xl:col-span-4">
                @can('manage invoices')
                    <x-dashboard.chart
                        id="chart-invoice-status"
                        type="doughnut"
                        :data="$invoiceStatusConfig"
                        height="260"
                        :aria-label="__('dashboard.chart_invoice_status_aria')"
                    >
                        <x-slot:header>
                            <h3 class="dash-chart-title m-0">{{ __('dashboard.chart_invoice_status_title') }}</h3>
                        </x-slot:header>
                    </x-dashboard.chart>
                @endcan
            </div>
        @endif

        <div class="{{ $showFinancialCharts ? 'xl:col-span-6' : 'xl:col-span-6' }}">
            <x-dashboard.chart
                id="chart-appointments"
                type="bar"
                :data="$appointmentsConfig"
                height="220"
                :aria-label="__('dashboard.chart_appointments_aria')"
            >
                <x-slot:header>
                    <h3 class="dash-chart-title m-0">{{ __('dashboard.chart_appointments_title') }}</h3>
                </x-slot:header>
            </x-dashboard.chart>
        </div>

        <div class="xl:col-span-6">
            <x-dashboard.chart
                id="chart-patients"
                type="line"
                :data="$patientsConfig"
                height="220"
                :aria-label="__('dashboard.chart_patients_aria')"
            >
                <x-slot:header>
                    <h3 class="dash-chart-title m-0">{{ __('dashboard.chart_patients_title') }}</h3>
                </x-slot:header>
            </x-dashboard.chart>
        </div>

        @if($showFinancialCharts)
            <div class="xl:col-span-6">
                <x-dashboard.chart
                    id="chart-payments"
                    type="bar"
                    :data="$paymentsConfig"
                    height="220"
                    :aria-label="__('dashboard.chart_payments_aria')"
                >
                    <x-slot:header>
                        <h3 class="dash-chart-title m-0">{{ __('dashboard.chart_payments_title') }}</h3>
                    </x-slot:header>
                </x-dashboard.chart>
            </div>
        @endif

        @if($showDoctorChart)
            <div class="xl:col-span-6">
                <x-dashboard.chart
                    id="chart-doctors"
                    type="bar"
                    :data="$doctorsConfig"
                    height="240"
                    :aria-label="__('dashboard.chart_doctors_aria')"
                >
                    <x-slot:header>
                        <h3 class="dash-chart-title m-0">{{ __('dashboard.chart_doctors_title') }}</h3>
                        <p class="dash-chart-sub m-0 mt-1">{{ __('dashboard.chart_doctors_sub') }}</p>
                    </x-slot:header>
                </x-dashboard.chart>
            </div>
        @endif
    </div>
</x-dashboard.section>
