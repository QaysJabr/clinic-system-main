@php
    use App\Enums\AppointmentStatus;
    $apptDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $stats = $appointmentStats ?? ['today' => 0, 'active' => 0, 'completed' => 0, 'cancelled' => 0];
    $hasFilters = request()->filled('patient') || request()->filled('doctor') || request()->filled('status') || request()->filled('date');
    $todayDate = now()->toDateString();
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $apptDir }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('appointments.heading_index') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('appointments.subtitle_index') }}</p>
        </div>
    </div>

    <x-appointment.nav active="list" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('appointments.stat_today') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('appointments.stat_active') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('appointments.stat_completed') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-800 dark:text-emerald-300">{{ number_format($stats['completed']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-slate-50/80 p-4 shadow-sm dark:border-[#374151] dark:bg-[#111827]/60">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('appointments.stat_cancelled') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-700 dark:text-slate-300">{{ number_format($stats['cancelled']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('appointments.index', ['date' => $todayDate]) }}" data-spa class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('date') === $todayDate ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('appointments.quick_today') }}</a>
        @foreach ([AppointmentStatus::Scheduled, AppointmentStatus::Confirmed, AppointmentStatus::Completed] as $quickStatus)
            <a href="{{ route('appointments.index', ['status' => $quickStatus->value]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('status') === $quickStatus->value ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
                <span class="h-2 w-2 rounded-full" style="background: {{ $quickStatus->color() }}"></span>
                {{ __($quickStatus->labelKey()) }}
            </a>
        @endforeach
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('appointments.filter_heading') }}</p>
        <form method="GET" action="{{ route('appointments.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('appointments.search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-3">
                        <label for="patient" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('appointments.label_patient_name') }}</label>
                        <input type="search" name="patient" id="patient" value="{{ request('patient') }}" placeholder="{{ __('appointments.placeholder_search') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-3">
                        <label for="doctor" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('appointments.label_doctor_name') }}</label>
                        <input type="search" name="doctor" id="doctor" value="{{ request('doctor') }}" placeholder="{{ __('appointments.placeholder_search') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-2">
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('appointments.label_status') }}</label>
                        <select name="status" id="status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                            <option value="">{{ __('appointments.filter_all') }}</option>
                            @foreach (AppointmentStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ __($status->labelKey()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('appointments.label_appointment_date') }}</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-2 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('appointments.btn_search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('appointments.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('appointments.btn_reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('appointments.list_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('appointments.table_title_all') }}</h2>
                    @if ($appointments->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('appointments.results_count', [
                                'from' => $appointments->firstItem(),
                                'to' => $appointments->lastItem(),
                                'total' => $appointments->total(),
                            ]) }}
                        </p>
                    @endif
                </div>
                <a href="{{ route('appointments.export', request()->query()) }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-[#0F4C81] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F4C81] shadow-sm transition hover:bg-blue-50 dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD] dark:hover:bg-[#1E3A5F]/50">{{ __('appointments.export_excel') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('appointments.th_patient') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('appointments.th_doctor') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('appointments.th_datetime') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('appointments.th_status') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('appointments.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            @php $statusEnum = AppointmentStatus::tryFrom($appointment->status); @endphp
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151] dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('appointments.edit', $appointment) }}" data-spa class="group flex min-w-0 items-center gap-2">
                                        @if ($statusEnum)
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white dark:ring-[#1F2937]" style="background: {{ $statusEnum->color() }}" aria-hidden="true"></span>
                                        @endif
                                        <span class="min-w-0 font-semibold text-gray-900 transition group-hover:text-[#0F4C81] dark:text-[#F3F4F6] dark:group-hover:text-[#93C5FD]">{{ optional($appointment->patient)->full_name ?? __('common.em_dash') }}</span>
                                    </a>
                                    @if (filled($appointment->reason))
                                        <p class="m-0 mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $appointment->reason }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ optional($appointment->doctor)->full_name ?? __('common.em_dash') }}</td>
                                <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">
                                    <span class="block font-medium text-gray-800 dark:text-gray-200">@safeDate($appointment->appointment_date)</span>
                                    <span class="block text-xs">{{ $appointment->start_time }}@if (filled($appointment->end_time)) – {{ $appointment->end_time }}@endif</span>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <x-appointment-status-badge :status="$appointment->status" />
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <a href="{{ route('appointments.edit', $appointment) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('common.edit') }}</a>
                                        <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="inline" data-confirm-title="{{ __('appointments.confirm_delete_title') }}" data-confirm="{{ __('appointments.confirm_delete_body') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-red-600 hover:underline dark:text-red-400">{{ __('common.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-gray-700 dark:text-gray-200">{{ __('appointments.empty_title') }}</p>
                                    <p class="m-0 mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $hasFilters ? __('appointments.empty_filtered') : __('appointments.empty_hint') }}</p>
                                    @if (! $hasFilters)
                                        <a href="{{ route('appointments.create') }}" data-spa class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('appointments.add_new') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($appointments->hasPages())
                <div class="border-t border-gray-100 bg-[#FAFBFC] px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/40 sm:px-5">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
