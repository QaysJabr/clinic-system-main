@php
    use App\Enums\AppointmentStatus;
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $currency = $snapshot['currency'] ?? null;
@endphp
<x-guest-layout
    :wide="true"
    :page-title="__('portal.title').' — '.($snapshot['clinicName'] ?? config('app.name'))"
    :meta-description="__('portal.meta')"
>
    <div class="mx-auto max-w-3xl" dir="{{ $dir }}">
        <header class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#0F4C81] to-[#1F7A8C] p-6 text-white shadow-lg dark:border-slate-700">
            <p class="m-0 text-xs font-bold uppercase tracking-wider text-white/70">{{ __('portal.kicker') }}</p>
            <h1 class="m-0 mt-2 text-2xl font-extrabold">{{ __('portal.welcome', ['name' => $patient->full_name]) }}</h1>
            <p class="m-0 mt-2 text-sm text-white/85">{{ $snapshot['clinicName'] }}</p>
            <p class="m-0 mt-1 text-xs text-white/70">{{ __('portal.file_number') }}: {{ $patient->file_number }}</p>
        </header>

        @if ((float) ($snapshot['outstandingTotal'] ?? 0) > 0)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/90 px-5 py-4 dark:border-amber-900/40 dark:bg-amber-950/25">
                <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">{{ __('portal.outstanding_total') }}</p>
                <p class="m-0 mt-1 text-2xl font-extrabold tabular-nums text-amber-950 dark:text-amber-100">
                    {{ number_format($snapshot['outstandingTotal'], 2) }}
                    @if ($currency)<span class="text-sm font-semibold">{{ $currency }}</span>@endif
                </p>
            </div>
        @endif

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-700 dark:bg-slate-800/60">
                <h2 class="m-0 text-base font-bold text-slate-900 dark:text-white">{{ __('portal.section_upcoming') }}</h2>
            </div>
            @if ($snapshot['upcomingAppointments']->isEmpty())
                <p class="m-0 px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('portal.empty_upcoming') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-start dark:border-slate-700 dark:bg-slate-800/40">
                                <th class="px-5 py-3 font-bold text-slate-600 dark:text-slate-300">{{ __('portal.th_date') }}</th>
                                <th class="px-5 py-3 font-bold text-slate-600 dark:text-slate-300">{{ __('portal.th_time') }}</th>
                                <th class="px-5 py-3 font-bold text-slate-600 dark:text-slate-300">{{ __('portal.th_doctor') }}</th>
                                <th class="px-5 py-3 font-bold text-slate-600 dark:text-slate-300">{{ __('portal.th_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($snapshot['upcomingAppointments'] as $appointment)
                                @php $status = AppointmentStatus::tryFrom($appointment->status); @endphp
                                <tr class="border-b border-slate-50 last:border-0 dark:border-slate-800">
                                    <td class="px-5 py-3 tabular-nums">@safeDate($appointment->appointment_date)</td>
                                    <td class="px-5 py-3 tabular-nums font-semibold">{{ $appointment->start_time }}</td>
                                    <td class="px-5 py-3">{{ optional($appointment->doctor)->full_name ?? __('common.em_dash') }}</td>
                                    <td class="px-5 py-3">
                                        @if ($status)
                                            <x-appointment-status-badge :status="$appointment->status" />
                                        @else
                                            {{ $appointment->status }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-700 dark:bg-slate-800/60">
                <h2 class="m-0 text-base font-bold text-slate-900 dark:text-white">{{ __('portal.section_invoices') }}</h2>
            </div>
            @if ($snapshot['openInvoices']->isEmpty())
                <p class="m-0 px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('portal.empty_invoices') }}</p>
            @else
                <ul class="m-0 divide-y divide-slate-100 p-0 list-none dark:divide-slate-800">
                    @foreach ($snapshot['openInvoices'] as $invoice)
                        @php $remaining = max(0, (float) $invoice->total - (float) $invoice->paid); @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                            <div>
                                <p class="m-0 font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                                @if ($invoice->due_date)
                                    <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('portal.due_date', ['date' => $invoice->due_date->format('d/m/Y')]) }}</p>
                                @endif
                            </div>
                            <div class="text-end">
                                <p class="m-0 text-sm font-bold tabular-nums text-slate-900 dark:text-white">
                                    {{ number_format($remaining, 2) }}
                                    @if ($currency) <span class="text-xs font-semibold text-slate-500">{{ $currency }}</span>@endif
                                </p>
                                <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $invoice->status === 'partial' ? __('common.partial') : __('common.unpaid') }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if ($snapshot['recentAppointments']->isNotEmpty())
            <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <h2 class="m-0 text-base font-bold text-slate-900 dark:text-white">{{ __('portal.section_recent') }}</h2>
                </div>
                <ul class="m-0 divide-y divide-slate-100 p-0 list-none dark:divide-slate-800">
                    @foreach ($snapshot['recentAppointments'] as $appointment)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <div>
                                <p class="m-0 font-medium text-slate-900 dark:text-white">@safeDate($appointment->appointment_date) · {{ $appointment->start_time }}</p>
                                <p class="m-0 mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ optional($appointment->doctor)->full_name ?? __('common.em_dash') }}</p>
                            </div>
                            @php $status = AppointmentStatus::tryFrom($appointment->status); @endphp
                            @if ($status)
                                <x-appointment-status-badge :status="$appointment->status" />
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <p class="m-0 text-center text-xs text-slate-500 dark:text-slate-400">{{ __('portal.privacy_note') }}</p>
    </div>
</x-guest-layout>
