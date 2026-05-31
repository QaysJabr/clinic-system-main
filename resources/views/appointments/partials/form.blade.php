@props([
    'appointment' => null,
    'patients',
    'doctors',
    'action',
    'method' => 'POST',
])

@php
    $isEdit = $appointment !== null;
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('appointments.form_section_participants') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <label for="patient_id" class="{{ $labelClass }}">{{ __('appointments.field_patient') }}</label>
                <select name="patient_id" id="patient_id" class="{{ $inputClass }}" required>
                    <option value="">{{ __('appointments.placeholder_select_patient') }}</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected((string) old('patient_id', $appointment?->patient_id) === (string) $patient->id)>{{ $patient->full_name }}</option>
                    @endforeach
                </select>
                @error('patient_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="doctor_id" class="{{ $labelClass }}">{{ __('appointments.field_doctor') }}</label>
                <select name="doctor_id" id="doctor_id" class="{{ $inputClass }}" required>
                    <option value="">{{ __('appointments.placeholder_select_doctor') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $appointment?->doctor_id) === (string) $doctor->id)>{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
                @error('doctor_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('appointments.form_section_schedule') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <label for="appointment_date" class="{{ $labelClass }}">{{ __('appointments.field_appointment_date') }}</label>
                <input type="date" name="appointment_date" id="appointment_date" value="{{ old('appointment_date', optional($appointment?->appointment_date)->format('Y-m-d')) }}" class="{{ $inputClass }}" required>
                @error('appointment_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="status" class="{{ $labelClass }}">{{ __('appointments.field_status') }}</label>
                <x-appointment-status-select
                    :selected="old('status', $appointment?->status ?? 'scheduled')"
                    class="{{ $inputClass }}"
                />
                @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="start_time" class="{{ $labelClass }}">{{ __('appointments.field_start_time') }}</label>
                <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $appointment?->start_time) }}" class="{{ $inputClass }}" required>
                @error('start_time')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="end_time" class="{{ $labelClass }}">{{ __('appointments.field_end_time') }}</label>
                <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $appointment?->end_time) }}" class="{{ $inputClass }}">
                @error('end_time')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            @include('appointments.partials.slot-picker', ['inputClass' => $inputClass, 'labelClass' => $labelClass])
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('appointments.form_section_details') }}</h2>
        </div>
        <div class="space-y-4 p-4 sm:p-5">
            <div>
                <label for="reason" class="{{ $labelClass }}">{{ __('appointments.field_reason') }}</label>
                <input type="text" name="reason" id="reason" value="{{ old('reason', $appointment?->reason) }}" class="{{ $inputClass }}">
                @error('reason')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="notes" class="{{ $labelClass }}">{{ __('appointments.field_notes') }}</label>
                <textarea name="notes" id="notes" rows="3" class="{{ $inputClass }}">{{ old('notes', $appointment?->notes) }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-[#374151]">
        <a href="{{ route('appointments.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.cancel') }}</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ $isEdit ? __('common.update') : __('common.save') }}
        </button>
    </div>
</form>
