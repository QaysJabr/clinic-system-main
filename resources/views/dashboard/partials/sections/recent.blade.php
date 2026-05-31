<x-dashboard.section :title="__('dashboard.recent_activity_heading')" compact>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @can('manage patients')
        <div class="dash-panel">
            <h3 class="dash-panel-title m-0">{{ __('dashboard.recent_patients_heading') }}</h3>
            <ul class="dash-list m-0 p-0 list-none">
                @forelse(($recentPatients ?? collect()) as $patient)
                    <li class="dash-list-item">
                        <a href="{{ route('patients.show', $patient) }}" class="dash-list-link font-medium" data-spa>{{ $patient->full_name }}</a>
                        <p class="dash-list-meta m-0 mt-0.5">{{ $patient->phone ?? __('dashboard.no_phone') }}</p>
                    </li>
                @empty
                    <li><x-dashboard.empty /></li>
                @endforelse
            </ul>
        </div>
        @endcan

        @can('manage appointments')
        <div class="dash-panel">
            <h3 class="dash-panel-title m-0">{{ __('dashboard.recent_appointments_heading') }}</h3>
            <ul class="dash-list m-0 p-0 list-none">
                @forelse(($recentAppointments ?? collect()) as $appointment)
                    <li class="dash-list-item">
                        <p class="m-0 text-sm font-medium text-slate-900 dark:text-slate-100">{{ optional($appointment->patient)->full_name ?? '—' }}</p>
                        <p class="dash-list-meta m-0 mt-0.5">@safeDate($appointment->appointment_date)</p>
                    </li>
                @empty
                    <li><x-dashboard.empty /></li>
                @endforelse
            </ul>
        </div>
        @endcan

        @can('manage invoices')
        <div class="dash-panel">
            <h3 class="dash-panel-title m-0">{{ __('invoices.dashboard_recent_invoices') }}</h3>
            <ul class="dash-list m-0 p-0 list-none">
                @forelse(($recentInvoices ?? collect()) as $invoice)
                    <li class="dash-list-item flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('invoices.show', $invoice) }}" class="dash-list-link font-medium" data-spa>{{ $invoice->invoice_number }}</a>
                            <p class="dash-list-meta m-0 mt-0.5">{{ optional($invoice->patient)->full_name ?? __('common.em_dash') }}</p>
                        </div>
                        <span class="dash-status-pill dash-status-pill--{{ $invoice->status }}">{{ number_format((float) $invoice->total, 2) }}</span>
                    </li>
                @empty
                    <li><x-dashboard.empty :message="__('invoices.dashboard_no_data')" /></li>
                @endforelse
            </ul>
        </div>
        @endcan

        @can('manage payments')
        <div class="dash-panel">
            <h3 class="dash-panel-title m-0">{{ __('payments.dashboard_recent_payments') }}</h3>
            <ul class="dash-list m-0 p-0 list-none">
                @forelse(($recentPayments ?? collect()) as $payment)
                    <li class="dash-list-item flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="m-0 text-sm font-medium text-slate-900 dark:text-slate-100">{{ optional(optional($payment->invoice)->patient)->full_name ?? __('common.em_dash') }}</p>
                            <p class="dash-list-meta m-0 mt-0.5">@safeDate($payment->payment_date)</p>
                        </div>
                        <span class="text-sm font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format((float) $payment->amount, 2) }}</span>
                    </li>
                @empty
                    <li><x-dashboard.empty :message="__('payments.dashboard_no_data')" /></li>
                @endforelse
            </ul>
        </div>
        @endcan
    </div>
</x-dashboard.section>

@can('view reports')
    <div class="mt-6">
        <a href="{{ route('reports.index') }}" class="dash-cta-primary" data-spa>{{ __('dashboard.reports_cta') }}</a>
    </div>
@endcan
