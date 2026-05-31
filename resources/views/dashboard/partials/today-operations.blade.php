<div class="dash-section-group">
    <x-dashboard.section-label bar="brand">{{ __('dashboard.section_operations') }}</x-dashboard.section-label>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    @can('manage appointments')
        <div class="dash-panel-colored border-t-4 border-t-violet-500 dark:border-t-violet-500/75">
            <div class="flex items-center justify-between gap-3 px-4 py-3 dash-panel-head--violet">
                <h3 class="m-0 text-sm font-bold text-violet-900 dark:text-violet-200">{{ __('dashboard.today_appointments_title') }}</h3>
                <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-violet-800 hover:underline dark:text-violet-300" data-spa>{{ __('dashboard.view_all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[280px] text-sm">
                    <thead>
                        <tr class="border-b border-violet-100/80 bg-violet-50/50 dark:border-violet-900/30 dark:bg-violet-950/20">
                            <th class="px-4 py-2 text-start text-xs font-bold text-violet-900/80 dark:text-violet-200/90">{{ __('dashboard.th_patient') }}</th>
                            <th class="px-4 py-2 text-start text-xs font-bold text-violet-900/80 dark:text-violet-200/90">{{ __('dashboard.th_doctor') }}</th>
                            <th class="px-4 py-2 text-end text-xs font-bold text-violet-900/80 dark:text-violet-200/90">{{ __('dashboard.th_time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayAppointmentsList ?? [] as $appointment)
                            <tr class="border-b border-slate-50 last:border-0 dark:border-[#374151]/60">
                                <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">{{ optional($appointment->patient)->full_name ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ optional($appointment->doctor)->full_name ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-end tabular-nums font-semibold text-violet-800 dark:text-violet-300">@safeDate($appointment->appointment_date, 'H:i')</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 sm:px-5">
                                    <x-dashboard.empty
                                        :message="__('dashboard.today_appointments_empty')"
                                        :hint="__('dashboard.today_appointments_empty_hint')"
                                        :cta-href="route('appointments.create')"
                                        :cta-label="__('dashboard.action_new_appointment')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcan

    @can('manage visits')
        <div class="dash-panel-colored border-t-4 border-t-indigo-500 dark:border-t-indigo-500/75">
            <div class="flex items-center justify-between gap-3 px-4 py-3 dash-panel-head--indigo">
                <h3 class="m-0 text-sm font-bold text-indigo-900 dark:text-indigo-200">{{ __('dashboard.today_visits_title') }}</h3>
                <a href="{{ route('visits.index') }}" class="text-xs font-bold text-indigo-800 hover:underline dark:text-indigo-300" data-spa>{{ __('dashboard.view_all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[280px] text-sm">
                    <thead>
                        <tr class="border-b border-indigo-100/80 bg-indigo-50/50 dark:border-indigo-900/30 dark:bg-indigo-950/20">
                            <th class="px-4 py-2 text-start text-xs font-bold text-indigo-900/80 dark:text-indigo-200/90">{{ __('dashboard.th_patient') }}</th>
                            <th class="px-4 py-2 text-start text-xs font-bold text-indigo-900/80 dark:text-indigo-200/90">{{ __('dashboard.th_status') }}</th>
                            <th class="px-4 py-2 text-end text-xs font-bold text-indigo-900/80 dark:text-indigo-200/90"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayVisitsQueue ?? [] as $visit)
                            <tr class="border-b border-slate-50 last:border-0 dark:border-[#374151]/60">
                                <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">{{ optional($visit->patient)->full_name ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $visit->statusBadgeClasses() }}">{{ $visit->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    <a href="{{ route('visits.edit', $visit) }}" class="text-xs font-bold text-indigo-800 hover:underline dark:text-indigo-300" data-spa>{{ __('dashboard.open_visit') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 sm:px-5">
                                    <x-dashboard.empty
                                        :message="__('dashboard.today_visits_empty')"
                                        :hint="__('dashboard.today_visits_empty_hint')"
                                        :cta-href="route('visits.create')"
                                        :cta-label="__('dashboard.action_new_visit')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcan
    </div>
</div>
