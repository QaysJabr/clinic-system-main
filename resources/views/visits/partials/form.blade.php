@props([
    'visit' => null,
    'patients',
    'doctors',
    'appointments',
    'soap' => [],
    'procRows' => [['name' => '', 'notes' => '']],
    'rxRows' => [['medication_name' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'notes' => '']],
    'action',
    'method' => 'POST',
    'isEdit' => false,
])

@php
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
    $procHeading = $isEdit ? __('visits.structured_procedures_heading_edit') : __('visits.structured_procedures_heading');
    $rxHeading = $isEdit ? __('visits.structured_rx_heading_edit') : __('visits.structured_rx_heading');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('visits.form_section_participants') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <label for="patient_id" class="{{ $labelClass }}">{{ __('visits.field_patient') }}</label>
                <select name="patient_id" id="patient_id" class="{{ $inputClass }}" required>
                    <option value="">{{ __('visits.placeholder_select_patient') }}</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected((string) old('patient_id', $visit?->patient_id ?? request('patient_id')) === (string) $patient->id)>{{ $patient->full_name }}</option>
                    @endforeach
                </select>
                @error('patient_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="doctor_id" class="{{ $labelClass }}">{{ __('visits.field_doctor') }}</label>
                <select name="doctor_id" id="doctor_id" class="{{ $inputClass }}" required>
                    <option value="">{{ __('visits.placeholder_select_doctor') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $visit?->doctor_id ?? request('doctor_id')) === (string) $doctor->id)>{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
                @error('doctor_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="appointment_id" class="{{ $labelClass }}">{{ __('visits.field_linked_appointment') }}</label>
                <select name="appointment_id" id="appointment_id" class="{{ $inputClass }}">
                    <option value="">{{ __('visits.placeholder_no_appointment') }}</option>
                    @foreach ($appointments as $appointment)
                        <option value="{{ $appointment->id }}" @selected((string) old('appointment_id', $visit?->appointment_id ?? request('appointment_id')) === (string) $appointment->id)>
                            {{ optional($appointment->patient)->full_name ?? __('common.em_dash') }} — @safeDate($appointment->appointment_date)
                        </option>
                    @endforeach
                </select>
                @error('appointment_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="visit_date" class="{{ $labelClass }}">{{ __('visits.field_visit_date') }}</label>
                <input type="date" name="visit_date" id="visit_date" value="{{ old('visit_date', optional($visit?->visit_date)->format('Y-m-d') ?: now()->toDateString()) }}" class="{{ $inputClass }}" required>
                @error('visit_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="status" class="{{ $labelClass }}">{{ __('visits.field_status') }}</label>
                <select name="status" id="status" class="{{ $inputClass }} sm:max-w-xs" required>
                    @foreach (['waiting', 'in_progress', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $visit?->status ?? 'waiting') === $status)>{{ __('visits.status_'.$status) }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    @include('visits.partials.soap-fields', ['soap' => $soap, 'inputClass' => $inputClass, 'labelClass' => $labelClass])

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('visits.form_section_clinical') }}</h2>
        </div>
        <div class="space-y-4 p-4 sm:p-5">
            <div>
                <label for="procedures" class="{{ $labelClass }}">{{ __('visits.field_procedures_free') }}</label>
                <textarea name="procedures" id="procedures" rows="3" class="{{ $inputClass }}">{{ old('procedures', $visit?->procedures) }}</textarea>
                @error('procedures')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="prescriptions" class="{{ $labelClass }}">{{ __('visits.field_prescriptions_free') }}</label>
                <textarea name="prescriptions" id="prescriptions" rows="3" class="{{ $inputClass }}">{{ old('prescriptions', $visit?->prescriptions) }}</textarea>
                @error('prescriptions')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-slate-50/50 shadow-sm dark:border-[#374151] dark:bg-[#111827]/60">
        <div class="border-b border-slate-200/80 px-4 py-4 dark:border-[#374151] sm:px-5">
            <p class="m-0 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ $procHeading }}</p>
            @if (! $isEdit)
                <p class="m-0 mt-1 text-xs text-gray-500 dark:text-slate-400">{{ __('visits.structured_procedures_hint') }}</p>
            @endif
        </div>
        <div class="p-4 sm:p-5">
            <div id="proc-rows" class="space-y-3">
                @foreach ($procRows as $i => $row)
                    @php $row = is_array($row) ? $row : []; @endphp
                    <div class="proc-row grid grid-cols-1 gap-3 md:grid-cols-2">
                        <input type="text" name="procedure_rows[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="{{ __('visits.placeholder_procedure_name') }}" class="{{ $inputClass }}">
                        <div class="flex gap-2">
                            <input type="text" name="procedure_rows[{{ $i }}][notes]" value="{{ $row['notes'] ?? '' }}" placeholder="{{ __('visits.placeholder_procedure_notes') }}" class="flex-1 {{ $inputClass }}">
                            @if ($i > 0)
                                <button type="button" class="remove-proc shrink-0 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-red-700">{{ __('visits.js_btn_remove') }}</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-proc" class="mt-3 text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('visits.btn_add_procedure_row') }}</button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-slate-50/50 shadow-sm dark:border-[#374151] dark:bg-[#111827]/60">
        <div class="border-b border-slate-200/80 px-4 py-4 dark:border-[#374151] sm:px-5">
            <p class="m-0 text-sm font-bold text-gray-800 dark:text-[#F3F4F6]">{{ $rxHeading }}</p>
        </div>
        <div class="p-4 sm:p-5">
            <div id="rx-rows" class="space-y-3">
                @foreach ($rxRows as $i => $row)
                    @php $row = is_array($row) ? $row : []; @endphp
                    <div class="rx-row grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        <input type="text" name="rx_rows[{{ $i }}][medication_name]" value="{{ $row['medication_name'] ?? '' }}" placeholder="{{ __('visits.placeholder_medication') }}" class="{{ $inputClass }}">
                        <input type="text" name="rx_rows[{{ $i }}][dosage]" value="{{ $row['dosage'] ?? '' }}" placeholder="{{ __('visits.placeholder_dosage') }}" class="{{ $inputClass }}">
                        <input type="text" name="rx_rows[{{ $i }}][frequency]" value="{{ $row['frequency'] ?? '' }}" placeholder="{{ __('visits.placeholder_frequency') }}" class="{{ $inputClass }}">
                        <input type="text" name="rx_rows[{{ $i }}][duration]" value="{{ $row['duration'] ?? '' }}" placeholder="{{ __('visits.placeholder_duration') }}" class="{{ $inputClass }}">
                        <input type="text" name="rx_rows[{{ $i }}][notes]" value="{{ $row['notes'] ?? '' }}" placeholder="{{ __('visits.js_placeholder_rx_notes') }}" class="md:col-span-2 {{ $inputClass }}">
                        @if ($i > 0)
                            <div class="md:col-span-3 flex justify-end">
                                <button type="button" class="remove-rx rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-red-700">{{ __('visits.js_btn_remove_row') }}</button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-rx" class="mt-3 text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('visits.btn_add_rx_row') }}</button>
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ $visit ? route('visits.show', $visit) : route('visits.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.cancel') }}</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.save') }}</button>
    </div>
</form>

@include('visits.partials.structured-rows-script')
