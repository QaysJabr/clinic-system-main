@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $expiringSoonList = ($clinicsExpiringSoon ?? collect()) instanceof \Illuminate\Support\Collection
        ? $clinicsExpiringSoon
        : collect();
    $expiredList = ($clinicsExpired ?? collect()) instanceof \Illuminate\Support\Collection
        ? $clinicsExpired
        : collect();
    $expiringDays = (int) config('platform.expiring_soon_days', 7);
@endphp
<div class="clinic-page w-full" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
    @include('platform.partials.nav', ['active' => 'dashboard'])
    <div class="mb-8">
        <h1 class="text-[26px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('platform.dashboard_title') }}</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('platform.dashboard_subtitle') }}</p>
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('platform.clinics.index') }}" data-spa class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M3 21h18"/></svg>
            {{ __('platform.quick_manage_clinics') }}
        </a>
        <a href="{{ route('admin.plans.index') }}" data-spa class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-bold text-violet-900 shadow-sm transition hover:bg-violet-100 dark:border-violet-800/50 dark:bg-violet-950/40 dark:text-violet-100 dark:hover:bg-violet-950/60">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
            {{ __('platform.quick_manage_plans') }}
        </a>
        <a href="{{ route('platform.clinics.index', ['subscription_status' => 'expiring_soon']) }}" data-spa class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-950 shadow-sm transition hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-950/35 dark:text-amber-100">
            {{ __('platform.filter_expiring_soon', ['days' => $expiringDays]) }}
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.total_revenue') }}</p>
            <p class="mt-2 text-2xl font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.monthly_revenue') }}</p>
            <p class="mt-2 text-2xl font-bold text-[#1F2937] dark:text-[#F3F4F6] m-0">{{ number_format($monthlyRevenue, 2) }}</p>
            <p class="mt-1 mb-0 text-xs text-slate-500 dark:text-[#9CA3AF]">
                {{ __('platform.revenue_breakdown', ['manual' => number_format($monthlyRevenueManual, 2), 'stripe' => number_format($monthlyRevenueStripe, 2)]) }}
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.total_clinics') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-800 dark:text-[#F3F4F6] m-0">{{ number_format($totalClinicsCount) }}</p>
            <p class="mt-1 mb-0 text-xs text-emerald-700 dark:text-emerald-300">+{{ number_format($newClinicsThisMonth) }} {{ __('platform.new_this_month') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.active_clinics') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300 m-0">{{ number_format($activeClinicsCount) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.expired_clinics') }}</p>
            <p class="mt-2 text-3xl font-bold text-rose-700 dark:text-rose-300 m-0">{{ number_format($expiredClinicsCount) }}</p>
            @if($expiredClinicsCount > 0)
                <a href="{{ route('platform.clinics.index', ['subscription_status' => 'expired']) }}" data-spa class="mt-1 inline-block text-xs font-semibold text-rose-800 underline dark:text-rose-200">{{ __('platform.view_all_expired') }}</a>
            @endif
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 dark:border-amber-900/40 dark:bg-amber-950/25">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-200">{{ __('platform.expiring_soon_clinics', ['days' => $expiringDays]) }}</p>
            <p class="mt-2 text-3xl font-bold text-amber-900 dark:text-amber-100 m-0">{{ number_format($expiringSoonCount ?? 0) }}</p>
            @if(($expiringSoonCount ?? 0) > 0)
                <a href="{{ route('platform.clinics.index', ['subscription_status' => 'expiring_soon']) }}" data-spa class="mt-1 inline-block text-xs font-semibold text-amber-900 underline dark:text-amber-200">{{ __('platform.view_all') }}</a>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.suspended_clinics') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-600 dark:text-[#9CA3AF] m-0">{{ number_format($suspendedClinicsCount) }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#374151] dark:bg-[#1F2937]">
        <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.revenue_last_6_months') }}</p>
        <div class="mt-4 flex h-36 items-end justify-between gap-2">
            @foreach (is_array($revenueByMonth) ? $revenueByMonth : [] as $row)
                @php
                    $row = is_array($row) ? $row : ['label' => '', 'amount' => 0, 'manual' => 0, 'stripe' => 0];
                    $amount = (float) ($row['amount'] ?? 0);
                    $manual = (float) ($row['manual'] ?? 0);
                    $stripe = (float) ($row['stripe'] ?? 0);
                    $pct = ($revenueChartMax ?? 1) > 0 ? min(100, ($amount / $revenueChartMax) * 100) : 0;
                @endphp
                <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                    <div class="flex h-28 w-full items-end justify-center gap-0.5 rounded-lg bg-slate-100 px-1 dark:bg-[#111827]">
                        @if($manual > 0)
                            @php $manualPct = $amount > 0 ? ($manual / $amount) * $pct : 0; @endphp
                            <div class="w-2/5 max-w-[1.25rem] rounded-t bg-emerald-600 dark:bg-emerald-500" style="height: {{ $manualPct }}%; min-height: {{ $manual > 0 ? '4px' : '0' }}" title="{{ __('platform.source_manual') }}"></div>
                        @endif
                        @if($stripe > 0)
                            @php $stripePct = $amount > 0 ? ($stripe / $amount) * $pct : 0; @endphp
                            <div class="w-2/5 max-w-[1.25rem] rounded-t bg-[#0F4C81] dark:bg-[#3B82F6]" style="height: {{ $stripePct }}%; min-height: {{ $stripe > 0 ? '4px' : '0' }}" title="{{ __('platform.source_stripe') }}"></div>
                        @endif
                        @if($amount <= 0)
                            <div class="h-0 w-4/5"></div>
                        @endif
                    </div>
                    <span class="truncate text-[11px] font-semibold text-slate-500 dark:text-[#9CA3AF]">{{ $row['label'] }}</span>
                    <span class="truncate text-xs font-bold text-slate-800 dark:text-[#F3F4F6]">{{ number_format($amount, 0) }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-600 dark:text-[#9CA3AF]">
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-emerald-600"></span>{{ __('platform.source_manual') }}</span>
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-[#0F4C81] dark:bg-[#3B82F6]"></span>{{ __('platform.source_stripe') }}</span>
        </div>
    </div>

    @if($expiringSoonList->isNotEmpty() || $expiredList->isNotEmpty())
        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @if($expiringSoonList->isNotEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/35 dark:text-amber-100">
                    <div class="flex items-center justify-between gap-2">
                        <p class="m-0 font-bold">{{ __('platform.expiring_soon_title', ['days' => $expiringDays]) }}</p>
                        <a href="{{ route('platform.clinics.index', ['subscription_status' => 'expiring_soon']) }}" data-spa class="text-xs font-semibold underline">{{ __('platform.view_all') }}</a>
                    </div>
                    <ul class="mt-3 list-none m-0 space-y-2 p-0">
                        @foreach ($expiringSoonList as $c)
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/60 px-3 py-2 dark:bg-black/20">
                                <span><span class="font-semibold">{{ $c->name }}</span> — {{ optional($c->subscription_expires_at)->format('d/m/Y H:i') }}</span>
                                <a href="{{ route('platform.clinics.subscription', $c) }}" data-spa class="shrink-0 text-xs font-bold underline">{{ __('platform.manage') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($expiredList->isNotEmpty())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-950 dark:border-rose-900/40 dark:bg-rose-950/35 dark:text-rose-100">
                    <div class="flex items-center justify-between gap-2">
                        <p class="m-0 font-bold">{{ __('platform.expired_list_title') }}</p>
                        <a href="{{ route('platform.clinics.index', ['subscription_status' => 'expired']) }}" data-spa class="text-xs font-semibold underline">{{ __('platform.view_all_expired') }}</a>
                    </div>
                    <ul class="mt-3 list-none m-0 space-y-2 p-0">
                        @foreach ($expiredList as $c)
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/60 px-3 py-2 dark:bg-black/20">
                                <span>
                                    <span class="font-semibold">{{ $c->name }}</span>
                                    @if($c->subscription_expires_at)
                                        ({{ $c->subscription_expires_at->format('d/m/Y') }})
                                    @endif
                                </span>
                                <a href="{{ route('platform.clinics.subscription', $c) }}" data-spa class="shrink-0 text-xs font-bold underline">{{ __('platform.manage') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-8">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('platform.recent_payments') }}</h2>
            <a href="{{ route('platform.payments.export') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-800 no-underline shadow-sm transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">
                {{ __('platform.export_payments_csv') }}
            </a>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-start dark:border-[#374151] dark:bg-[#111827]">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_clinic') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_amount') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_source') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_paid_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentSubscriptionPayments as $sp)
                        <tr class="border-b border-slate-100 dark:border-[#374151]">
                            <td class="px-4 py-3 font-medium">
                                @if($sp->clinic)
                                    <a href="{{ route('platform.clinics.subscription', $sp->clinic) }}" data-spa class="font-semibold text-[#1F7A8C] no-underline hover:text-[#0F4C81] dark:text-[#5EEAD4]">{{ $sp->clinic->name }}</a>
                                @else
                                    {{ __('common.em_dash') }}
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ number_format((float) $sp->amount, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($sp->source === \App\Models\SubscriptionPayment::SOURCE_STRIPE)
                                    <span class="rounded-full bg-[#0F4C81]/10 px-2 py-0.5 text-xs font-semibold text-[#0F4C81] dark:bg-blue-950/40 dark:text-[#93C5FD]">{{ __('platform.source_stripe') }}</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">{{ __('platform.source_manual') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ $sp->paid_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-[#9CA3AF]">{{ __('platform.no_payments_yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
