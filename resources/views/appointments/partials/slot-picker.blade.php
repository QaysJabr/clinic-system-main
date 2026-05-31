@php
    $labelClass = $labelClass ?? 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
@endphp
<div
    class="sm:col-span-2"
    data-appointment-slots
    data-slots-url="{{ route('appointments.slots') }}"
    data-doctor-select="#doctor_id"
    data-date-select="#appointment_date"
    data-start-select="#start_time"
>
    <label class="{{ $labelClass }}">{{ __('appointments.available_slots') }}</label>
    <div data-slot-grid class="mt-2 grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8"></div>
    <p data-slot-empty class="mt-2 hidden text-sm text-gray-500 dark:text-[#9CA3AF]">{{ __('appointments.booking_no_slots') }}</p>
</div>
