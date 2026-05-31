@props([
    'staff' => null,
    'linkableUsers',
    'action',
    'method' => 'POST',
])

@php
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('staff.card_title') }}</h2>
            <p class="m-0 mt-1 text-sm text-gray-500 dark:text-slate-400">{{ $staff ? __('staff.card_intro_edit') : __('staff.card_intro_create') }}</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div class="sm:col-span-2">
                <label for="full_name" class="{{ $labelClass }}">{{ __('staff.label_full_name') }}</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $staff?->full_name) }}" required
                    class="@error('full_name') border-red-300 ring-red-200 @enderror {{ $inputClass }}">
                @error('full_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="role_type" class="{{ $labelClass }}">{{ __('staff.label_role_type') }}</label>
                <select name="role_type" id="role_type" required class="@error('role_type') border-red-300 @enderror {{ $inputClass }}">
                    @if (! $staff)
                        <option value="">{{ __('staff.placeholder_select_role') }}</option>
                    @endif
                    @foreach (\App\Models\Staff::roleTypeOptions() as $value => $label)
                        <option value="{{ $value }}" @selected(old('role_type', $staff?->role_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role_type')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="status" class="{{ $labelClass }}">{{ __('staff.label_status') }}</label>
                <select name="status" id="status" required class="@error('status') border-red-300 @enderror {{ $inputClass }}">
                    <option value="active" @selected(old('status', $staff?->status ?? 'active') === 'active')>{{ __('staff.status_active') }}</option>
                    <option value="inactive" @selected(old('status', $staff?->status) === 'inactive')>{{ __('staff.status_inactive') }}</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="{{ $labelClass }}">{{ __('staff.label_phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $staff?->phone) }}" class="@error('phone') border-red-300 @enderror {{ $inputClass }}">
                @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="{{ $labelClass }}">{{ __('staff.label_email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', $staff?->email) }}" class="@error('email') border-red-300 @enderror {{ $inputClass }}">
                @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="user_id" class="{{ $labelClass }}">{{ __('staff.label_user_link') }}</label>
                <select name="user_id" id="user_id" class="@error('user_id') border-red-300 @enderror {{ $inputClass }}">
                    <option value="">{{ __('staff.placeholder_no_user') }}</option>
                    @foreach ($linkableUsers as $u)
                        <option value="{{ $u->id }}" @selected((string) old('user_id', $staff?->user_id) === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                <p class="m-0 mt-1 text-xs text-gray-500 dark:text-slate-400">{{ __('staff.hint_one_user_staff') }}</p>
                @error('user_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ route('staff.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('staff.btn_back') }}</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ $staff ? __('staff.btn_update') : __('staff.btn_save') }}</button>
    </div>
</form>
