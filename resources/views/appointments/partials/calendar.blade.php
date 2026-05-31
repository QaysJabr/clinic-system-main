@php
    use App\Enums\AppointmentStatus;
    $apptDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@if (! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div
    class="max-w-[1600px] mx-auto"
    dir="{{ $apptDir }}"
    id="appointment-calendar-root"
    data-appointment-calendar
    data-events-url="{{ route('appointments.calendar.events') }}"
    data-reschedule-url="{{ url('appointments/__ID__/calendar/reschedule') }}"
    data-status-url="{{ url('appointments/__ID__/calendar/status') }}"
    data-check-in-url="{{ url('appointments/__ID__/check-in') }}"
    data-create-url="{{ route('appointments.create') }}"
    data-locale="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-dir="{{ $apptDir }}"
    data-initial-view="{{ request('view', 'timeGridWeek') }}"
    data-filter-doctor="{{ request('doctor_id', '') }}"
>
    <div class="mb-6">
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('appointments.heading_calendar') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('appointments.subtitle_calendar') }}</p>
    </div>

    <x-appointment.nav active="calendar" />

    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="min-w-[200px] flex-1">
            <label for="calendar-doctor-filter" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('appointments.field_doctor') }}</label>
            <select id="calendar-doctor-filter" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                <option value="">{{ __('appointments.filter_all_doctors') }}</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>{{ $doctor->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap gap-2 pb-0.5">
            @foreach (AppointmentStatus::cases() as $status)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 dark:border-[#4B5563] dark:text-[#E5E7EB]">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $status->color() }}"></span>
                    {{ __($status->labelKey()) }}
                </span>
            @endforeach
        </div>
    </div>

    <div id="appointment-calendar" class="appointment-calendar-shell min-h-[480px] rounded-xl border border-gray-200 bg-white p-2 shadow-sm dark:border-[#374151] dark:bg-[#1F2937] sm:min-h-[640px]"></div>

    <dialog id="appointment-calendar-modal" class="appointment-calendar-dialog w-[min(100%,28rem)] rounded-xl border border-gray-200 bg-white p-0 shadow-xl backdrop:bg-black/40 dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-[#374151]">
            <h2 class="m-0 text-lg font-bold text-gray-900 dark:text-[#F3F4F6]" id="appointment-calendar-modal-title"></h2>
            <p class="m-0 mt-1 text-sm text-gray-500 dark:text-[#9CA3AF]" id="appointment-calendar-modal-meta"></p>
        </div>
        <div class="space-y-3 px-5 py-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]" for="appointment-calendar-status">{{ __('appointments.field_status') }}</label>
            <select id="appointment-calendar-status" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                @foreach (AppointmentStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ __($status->labelKey()) }}</option>
                @endforeach
            </select>
            <p id="appointment-calendar-modal-error" class="hidden text-sm text-red-600"></p>
        </div>
        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 px-5 py-4 dark:border-[#374151]">
            <button type="button" id="appointment-calendar-modal-close" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:text-[#E5E7EB]">{{ __('common.cancel') }}</button>
            <button type="button" id="appointment-calendar-check-in" class="rounded-lg border border-violet-300 px-4 py-2 text-sm font-semibold text-violet-700 dark:border-violet-500/50 dark:text-violet-300">{{ __('appointments.btn_check_in') }}</button>
            <a href="#" id="appointment-calendar-edit" data-spa class="rounded-lg border border-[#0F4C81] px-4 py-2 text-sm font-semibold text-[#0F4C81] dark:border-[#3B82F6] dark:text-[#93C5FD]">{{ __('common.edit') }}</a>
            <button type="button" id="appointment-calendar-save-status" class="rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-semibold text-white dark:bg-[#3B82F6]">{{ __('common.save') }}</button>
        </div>
    </dialog>
</div>
