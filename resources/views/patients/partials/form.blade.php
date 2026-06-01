@props([
    'patient' => null,
    'action',
    'method' => 'POST',
])

@php
    $isEdit = $patient !== null;
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
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('patients.form_section_identity') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            @if ($isEdit)
                <div>
                    <label for="file_number" class="{{ $labelClass }}">{{ __('patients.field_file_number') }}</label>
                    <input type="text" id="file_number" value="{{ $patient->file_number }}" class="{{ $inputClass }} cursor-not-allowed bg-slate-50 dark:bg-slate-900/60" readonly>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('patients.file_number_readonly_hint') }}</p>
                </div>
            @else
                <div class="sm:col-span-2 rounded-lg border border-sky-200 bg-sky-50/80 px-3 py-2.5 text-sm text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                    {{ __('patients.file_number_auto_hint') }}
                </div>
            @endif
            <div>
                <label for="full_name" class="{{ $labelClass }}">{{ __('patients.field_full_name') }}</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $patient?->full_name) }}" class="{{ $inputClass }}" required autocomplete="name">
                @error('full_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="national_id" class="{{ $labelClass }}">{{ __('patients.field_national_id') }}</label>
                <input type="text" name="national_id" id="national_id" value="{{ old('national_id', $patient?->national_id) }}" class="{{ $inputClass }}" inputmode="numeric">
                @error('national_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="status" class="{{ $labelClass }}">{{ __('patients.field_status') }}</label>
                <select name="status" id="status" class="{{ $inputClass }}" required>
                    <option value="active" {{ old('status', $patient?->status ?? 'active') === 'active' ? 'selected' : '' }}>{{ __('patients.status_active') }}</option>
                    <option value="inactive" {{ old('status', $patient?->status) === 'inactive' ? 'selected' : '' }}>{{ __('patients.status_inactive') }}</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('patients.form_section_contact') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <label for="phone" class="{{ $labelClass }}">{{ __('patients.field_phone') }}</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $patient?->phone) }}" class="{{ $inputClass }}" autocomplete="tel">
                @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="date_of_birth" class="{{ $labelClass }}">{{ __('patients.field_date_of_birth') }}</label>
                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', optional($patient?->date_of_birth)->format('Y-m-d')) }}" class="{{ $inputClass }}">
                @error('date_of_birth')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="gender" class="{{ $labelClass }}">{{ __('patients.field_gender') }}</label>
                <select name="gender" id="gender" class="{{ $inputClass }}">
                    <option value="">{{ __('patients.gender_select') }}</option>
                    <option value="male" {{ old('gender', $patient?->gender) === 'male' ? 'selected' : '' }}>{{ __('patients.gender_male') }}</option>
                    <option value="female" {{ old('gender', $patient?->gender) === 'female' ? 'selected' : '' }}>{{ __('patients.gender_female') }}</option>
                </select>
                @error('gender')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="{{ $labelClass }}">{{ __('patients.field_address') }}</label>
                <textarea name="address" id="address" rows="2" class="{{ $inputClass }}">{{ old('address', $patient?->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('patients.form_section_notes') }}</h2>
        </div>
        <div class="p-4 sm:p-5">
            <label for="notes" class="{{ $labelClass }}">{{ __('patients.field_notes') }}</label>
            <textarea name="notes" id="notes" rows="3" class="{{ $inputClass }}">{{ old('notes', $patient?->notes) }}</textarea>
            @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-[#374151]">
        <a href="{{ $isEdit ? route('patients.show', $patient) : route('patients.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('patients.cancel') }}</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ $isEdit ? __('patients.update') : __('patients.save') }}
        </button>
    </div>
</form>
