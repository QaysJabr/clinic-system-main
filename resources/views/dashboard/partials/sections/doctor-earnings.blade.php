@canany(['view doctor earnings', 'manage doctor earnings'])
@isset($doctorEarning)
<x-dashboard.section
    id="dash-doctor-earn"
    :title="__('dashboard.doctor_earn_section_title')"
    :intro="($dashboardStatsPersonal ?? false) ? __('dashboard.doctor_earn_intro_personal') : __('dashboard.doctor_earn_intro_all')"
>
    <x-slot:actions>
        <a href="{{ route('doctor-earnings.index') }}" class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-teal-300" data-spa>{{ __('dashboard.doctor_earn_view_full') }}</a>
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <x-dashboard.kpi
            :title="__('dashboard.doctor_earn_total_title')"
            :value="number_format($doctorEarning['total'] ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="($dashboardStatsPersonal ?? false) ? __('dashboard.doctor_earn_total_hint_personal') : __('dashboard.doctor_earn_total_hint_all')"
            :badge="__('dashboard.doctor_earn_badge_share')"
            accent="cyan"
        />
        <x-dashboard.kpi
            :title="__('dashboard.doctor_earn_pending_title')"
            :value="number_format($doctorEarning['pending'] ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.doctor_earn_pending_hint')"
            :badge="__('dashboard.doctor_earn_badge_share')"
            accent="amber"
        />
        <x-dashboard.kpi
            :title="__('dashboard.doctor_earn_paid_title')"
            :value="number_format($doctorEarning['paid'] ?? 0, 2).(filled($cur ?? '') ? ' '.$cur : '')"
            :hint="__('dashboard.doctor_earn_paid_hint')"
            :badge="__('dashboard.doctor_earn_badge_share')"
            accent="emerald"
        />
    </div>

    <x-dashboard.table min-width="720">
        <x-slot:header>
            <h3 class="m-0 text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('dashboard.doctor_earn_monthly_title') }}</h3>
            <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ ($dashboardStatsPersonal ?? false) ? __('dashboard.doctor_earn_monthly_sub_personal') : __('dashboard.doctor_earn_monthly_sub_all') }}</p>
        </x-slot:header>
        <thead>
            <tr>
                <th>{{ __('dashboard.doctor_earn_th_month') }}</th>
                <th>{{ __('dashboard.doctor_earn_th_pending') }}</th>
                <th>{{ __('dashboard.doctor_earn_th_paid') }}</th>
                <th>{{ __('dashboard.doctor_earn_th_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctorEarning['monthly'] ?? [] as $row)
                <tr>
                    <td class="font-medium">{{ $row['label'] }}</td>
                    <td class="tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($row['pending'], 2) }}</td>
                    <td class="tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($row['paid'], 2) }}</td>
                    <td class="tabular-nums font-semibold">{{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <x-dashboard.empty />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-dashboard.table>
</x-dashboard.section>
@endisset
@endcanany
