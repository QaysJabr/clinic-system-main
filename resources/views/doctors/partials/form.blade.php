@props([
    'doctor' => null,
    'linkedStaff' => collect(),
    'action',
    'method' => 'POST',
    'linkOnly' => false,
])

@php
    $isEdit = $doctor !== null;
    $linkedStaffId = old('staff_id', $doctor?->staff_id);
    $hasStaffLink = filled($linkedStaffId);
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" id="doctor-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($linkOnly)
        <div class="rounded-xl border border-sky-200 bg-sky-50/80 px-4 py-3 text-sm text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
            <p class="m-0">{{ __('doctors.add_via_staff_hint') }}</p>
            <p class="m-0 mt-2">
                <a href="{{ route('staff.create', ['role' => 'doctor']) }}" data-spa class="font-bold text-[#0F4C81] underline dark:text-[#93C5FD]">{{ __('doctors.add_via_staff_link') }}</a>
            </p>
        </div>
    @endif

    @if ($linkOnly || ($isEdit && $hasStaffLink))
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.form_section_staff_link') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                @if ($linkOnly)
                    <label for="staff_id" class="{{ $labelClass }}">{{ __('doctors.staff_pick_label') }}</label>
                    <select name="staff_id" id="staff_id" required class="{{ $inputClass }}">
                        <option value="">{{ __('doctors.staff_none_option') }}</option>
                        @foreach ($linkedStaff as $s)
                            <option value="{{ $s->id }}" @selected((string) $linkedStaffId === (string) $s->id)>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                    @error('staff_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    @if ($linkedStaff->isEmpty())
                        <p class="mt-2 text-sm text-amber-800 dark:text-amber-200">{{ __('doctors.no_staff_without_doctor') }}</p>
                    @endif
                @else
                    <p class="m-0 text-sm text-gray-700 dark:text-slate-300">
                        {{ __('doctors.linked_staff_readonly', ['name' => $doctor->staff?->full_name ?? $doctor->full_name]) }}
                        <a href="{{ route('staff.edit', $doctor->staff_id) }}" data-spa class="ms-1 font-semibold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('doctors.edit_staff_record') }}</a>
                    </p>
                    <input type="hidden" name="staff_id" value="{{ $doctor->staff_id }}">
                @endif
                <div id="staff-summary" class="mt-3 hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300"></div>
            </div>
        </div>
    @endif

    <div id="doctor-shared-fields" class="{{ ($linkOnly || ($isEdit && $hasStaffLink)) ? 'hidden' : '' }} space-y-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.form_section_profile') }}</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
                <div class="sm:col-span-2">
                    <label for="full_name" class="{{ $labelClass }}">{{ __('doctors.field_full_name') }}</label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $doctor?->full_name) }}" class="{{ $inputClass }}" @if($linkOnly) disabled @else required @endif autocomplete="name">
                    @error('full_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="{{ $labelClass }}">{{ __('doctors.field_status') }}</label>
                    <select name="status" id="status" class="{{ $inputClass }}" @if($linkOnly) disabled @endif>
                        <option value="active" {{ old('status', $doctor?->status ?? 'active') === 'active' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                        <option value="inactive" {{ old('status', $doctor?->status) === 'inactive' ? 'selected' : '' }}>{{ __('common.inactive') }}</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.form_section_contact') }}</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
                <div>
                    <label for="phone" class="{{ $labelClass }}">{{ __('doctors.field_phone') }}</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $doctor?->phone) }}" class="{{ $inputClass }}" @if($linkOnly) disabled @endif autocomplete="tel">
                    @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="{{ $labelClass }}">{{ __('doctors.field_email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $doctor?->email) }}" class="{{ $inputClass }}" @if($linkOnly) disabled @endif autocomplete="email">
                    @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                @if (! $linkOnly && ! ($isEdit && $hasStaffLink))
                    <div class="sm:col-span-2">
                        <label for="staff_id" class="{{ $labelClass }}">{{ __('doctors.staff_link_label') }}</label>
                        <select name="staff_id" id="staff_id_legacy" class="{{ $inputClass }}">
                            <option value="">{{ __('doctors.staff_none_option') }}</option>
                            @foreach ($linkedStaff as $s)
                                <option value="{{ $s->id }}" @selected((string) $linkedStaffId === (string) $s->id)>{{ $s->full_name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('doctors.staff_link_hint') }}</p>
                        @error('staff_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('doctors.form_section_clinical') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <label for="specialty" class="{{ $labelClass }}">{{ __('doctors.field_specialty') }}</label>
                <input type="text" name="specialty" id="specialty" value="{{ old('specialty', $doctor?->specialty) }}" class="{{ $inputClass }}" placeholder="{{ __('doctors.specialty_placeholder') }}">
                @error('specialty')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="room_number" class="{{ $labelClass }}">{{ __('doctors.field_room') }}</label>
                <input type="text" name="room_number" id="room_number" value="{{ old('room_number', $doctor?->room_number) }}" class="{{ $inputClass }}">
                @error('room_number')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="license_number" class="{{ $labelClass }}">{{ __('doctors.field_license') }}</label>
                <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $doctor?->license_number) }}" class="{{ $inputClass }}">
                @error('license_number')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="notes" class="{{ $labelClass }}">{{ __('doctors.field_notes') }}</label>
                <textarea name="notes" id="notes" rows="3" class="{{ $inputClass }}">{{ old('notes', $doctor?->notes) }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-[#374151]">
        <a href="{{ route('doctors.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('doctors.cancel') }}</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ $isEdit ? __('doctors.update') : __('doctors.save') }}
        </button>
    </div>
</form>

@if ($linkOnly && $linkedStaff->isNotEmpty())
<script>
(function () {
    const staffData = @json($linkedStaff->map(fn ($s) => [
        'id' => $s->id,
        'full_name' => $s->full_name,
        'phone' => $s->phone,
        'email' => $s->email,
        'status' => $s->status,
    ])->values());
    const select = document.getElementById('staff_id');
    const summary = document.getElementById('staff-summary');
    if (!select || !summary) return;
    const render = () => {
        const row = staffData.find((s) => String(s.id) === select.value);
        if (!row) {
            summary.classList.add('hidden');
            summary.textContent = '';
            return;
        }
        summary.classList.remove('hidden');
        summary.innerHTML = '<strong>' + row.full_name + '</strong><br>' +
            (row.phone || '—') + ' · ' + (row.email || '—') + ' · ' + row.status;
    };
    select.addEventListener('change', render);
    render();
})();
</script>
@endif
