@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
    $isEdit = (bool) ($user ?? null);
    $roleLabels = [
        'admin' => __('chat.role_admin'),
        'clinic_owner' => __('settings.users_role_clinic_owner'),
        'doctor' => __('chat.role_doctor'),
        'receptionist' => __('chat.role_receptionist'),
        'accountant' => __('chat.role_accountant'),
    ];
    $selectedRole = old('role', $isEdit ? $user->roles->first()?->name : null);
@endphp

<form action="{{ $action }}" method="POST" class="space-y-6" novalidate>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('settings.users_section_identity') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div>
                    <label for="name" class="{{ $labelClass }}">{{ __('settings.users_th_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $isEdit ? $user->name : '') }}" required
                        class="{{ $inputClass }} @error('name') border-red-300 ring-red-200 @enderror">
                    @error('name')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="{{ $labelClass }}">{{ __('settings.users_th_email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $isEdit ? $user->email : '') }}" required autocomplete="username"
                        class="{{ $inputClass }} @error('email') border-red-300 ring-red-200 @enderror">
                    @error('email')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('settings.users_section_access') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="role" class="{{ $labelClass }}">{{ __('settings.users_label_role') }}</label>
                    <select name="role" id="role" required class="{{ $inputClass }} @error('role') border-red-300 ring-red-200 @enderror">
                        @unless($isEdit)
                            <option value="" @selected($selectedRole === null || $selectedRole === '')>{{ __('settings.users_role_placeholder') }}</option>
                        @endunless
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ $roleLabels[$role->name] ?? $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="{{ $labelClass }}">{{ $isEdit ? __('settings.users_label_password_optional') : __('settings.users_label_password') }}</label>
                    <input type="password" name="password" id="password" @unless($isEdit) required @endunless autocomplete="new-password"
                        class="{{ $inputClass }} @error('password') border-red-300 ring-red-200 @enderror">
                    @if ($isEdit)
                        <p class="m-0 mt-1.5 text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.users_password_optional_hint') }}</p>
                    @endif
                    @error('password')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="{{ $labelClass }}">{{ __('settings.users_label_password_confirm') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" @unless($isEdit) required @endunless autocomplete="new-password"
                        class="{{ $inputClass }} @error('password_confirmation') border-red-300 ring-red-200 @enderror">
                    @error('password_confirmation')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <a href="{{ route('users.index') }}" data-spa class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('settings.users_back_to_list') }}</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ $isEdit ? __('common.update') : __('settings.users_btn_create') }}
        </button>
    </div>
</form>
