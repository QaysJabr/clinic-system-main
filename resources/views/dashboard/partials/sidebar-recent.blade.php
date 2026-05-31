<div class="space-y-6">
    <div class="dash-panel-colored overflow-hidden border-t-4 border-t-sky-500 dark:border-t-sky-500/70">
        <x-dashboard.activity-feed :items="$activityStream ?? []" class="!m-0 !rounded-none !border-0 !bg-transparent !shadow-none !ring-0" />
    </div>

    @can('manage invoices')
        <div class="dash-panel-colored border-t-4 border-t-amber-500 dark:border-t-amber-500/70">
            <div class="px-4 py-3 dash-panel-head--amber">
                <h3 class="m-0 text-sm font-bold text-amber-900 dark:text-amber-200">{{ __('invoices.dashboard_recent_invoices') }}</h3>
            </div>
            <ul class="m-0 divide-y divide-amber-100/60 p-0 list-none dark:divide-[#374151]">
                @forelse(($recentInvoices ?? collect()) as $invoice)
                    <li class="px-4 py-3 hover:bg-amber-50/40 dark:hover:bg-amber-950/15">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-semibold text-amber-900 hover:underline dark:text-amber-200" data-spa>{{ $invoice->invoice_number }}</a>
                        <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ optional($invoice->patient)->full_name ?? __('common.em_dash') }}</p>
                        <p class="m-0 mt-1 text-sm font-bold tabular-nums text-amber-800 dark:text-amber-300">{{ number_format((float) $invoice->total, 2) }}</p>
                    </li>
                @empty
                    <li class="px-4 py-2">
                        <x-dashboard.empty
                            :message="__('invoices.dashboard_no_data')"
                            :hint="__('dashboard.empty_invoices_hint')"
                            :cta-href="route('invoices.create')"
                            :cta-label="__('dashboard.action_new_invoice')"
                            class="!py-6"
                        />
                    </li>
                @endforelse
            </ul>
        </div>
    @endcan

    @can('manage payments')
        <div class="dash-panel-colored border-t-4 border-t-emerald-500 dark:border-t-emerald-500/75">
            <div class="px-4 py-3 dash-panel-head--emerald">
                <h3 class="m-0 text-sm font-bold text-emerald-900 dark:text-emerald-200">{{ __('payments.dashboard_recent_payments') }}</h3>
            </div>
            <ul class="m-0 divide-y divide-emerald-100/60 p-0 list-none dark:divide-[#374151]">
                @forelse(($recentPayments ?? collect()) as $payment)
                    <li class="px-4 py-3 flex items-center justify-between gap-2 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/15">
                        <div class="min-w-0">
                            <p class="m-0 text-sm font-medium text-slate-900 dark:text-slate-100">{{ optional(optional($payment->invoice)->patient)->full_name ?? __('common.em_dash') }}</p>
                            <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">@safeDate($payment->payment_date)</p>
                        </div>
                        <span class="shrink-0 text-sm font-bold tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format((float) $payment->amount, 2) }}</span>
                    </li>
                @empty
                    <li class="px-4 py-2">
                        <x-dashboard.empty
                            :message="__('payments.dashboard_no_data')"
                            :hint="__('dashboard.empty_payments_hint')"
                            :cta-href="route('invoices.index')"
                            :cta-label="__('dashboard.empty_payments_cta')"
                            class="!py-6"
                        />
                    </li>
                @endforelse
            </ul>
        </div>
    @endcan

    @can('manage patients')
        <div class="dash-panel-colored border-t-4 border-t-violet-500 dark:border-t-violet-500/75">
            <div class="px-4 py-3 dash-panel-head--violet">
                <h3 class="m-0 text-sm font-bold text-violet-900 dark:text-violet-200">{{ __('dashboard.recent_patients_heading') }}</h3>
            </div>
            <ul class="m-0 divide-y divide-violet-100/60 p-0 list-none dark:divide-[#374151]">
                @forelse(($recentPatients ?? collect()) as $patient)
                    <li class="px-4 py-3 hover:bg-violet-50/40 dark:hover:bg-violet-950/15">
                        <a href="{{ route('patients.show', $patient) }}" class="text-sm font-semibold text-violet-900 hover:underline dark:text-violet-200" data-spa>{{ $patient->full_name }}</a>
                        <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $patient->phone ?? __('dashboard.no_phone') }}</p>
                    </li>
                @empty
                    <li class="px-4 py-2">
                        <x-dashboard.empty
                            :message="__('dashboard.no_data')"
                            :hint="__('dashboard.empty_patients_hint')"
                            :cta-href="route('patients.create')"
                            :cta-label="__('dashboard.action_new_patient')"
                            :cta-no-spa="true"
                            class="!py-6"
                        />
                    </li>
                @endforelse
            </ul>
        </div>
    @endcan
</div>
