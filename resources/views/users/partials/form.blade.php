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
    $linkableStaff = $linkableStaff ?? collect();
    $defaultMode = old('account_mode', $defaultMode ?? ($linkableStaff->isNotEmpty() ? 'from_staff' : 'new'));
    $staffPickerData = $linkableStaff->map(fn ($s) => [
        'id' => $s->id,
        'full_name' => $s->full_name,
        'email' => $s->email,
        'phone' => $s->phone,
        'role_type' => $s->role_type,
        'role_label' => $s->roleTypeLabel(),
    ])->values();
@endphp

<form
    action="{{ $action }}"
    method="POST"
    class="space-y-6"
    novalidate
    id="{{ $isEdit ? 'user-edit-form' : 'user-create-form' }}"
    @unless($isEdit)
        data-staff-picker="{{ $staffPickerData->toJson() }}"
        data-no-email-label="{{ __('settings.users_staff_no_email') }}"
    @endunless
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @unless($isEdit)
        <div class="rounded-xl border border-sky-200 bg-sky-50/90 px-4 py-3 text-sm text-sky-950 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-100">
            <p class="m-0 font-semibold">{{ __('settings.users_create_hint_title') }}</p>
            <p class="m-0 mt-1">{{ __('settings.users_create_hint_body') }}</p>
        </div>

        <fieldset class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <legend class="sr-only">{{ __('settings.users_account_mode_legend') }}</legend>
            <div class="flex flex-col gap-2 border-b border-gray-100 bg-gray-50/80 p-4 sm:flex-row sm:gap-4 dark:border-[#374151] dark:bg-[#111827]/90">
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-800 dark:text-[#F3F4F6]">
                    <input type="radio" name="account_mode" value="from_staff" @checked($defaultMode === 'from_staff') class="text-[#0F4C81] focus:ring-[#0F4C81]/30">
                    {{ __('settings.users_mode_from_staff') }}
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-800 dark:text-[#F3F4F6]">
                    <input type="radio" name="account_mode" value="new" @checked($defaultMode === 'new') class="text-[#0F4C81] focus:ring-[#0F4C81]/30">
                    {{ __('settings.users_mode_new_account') }}
                </label>
            </div>
        </fieldset>
    @endunless

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('settings.users_section_identity') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            @unless($isEdit)
                <div id="user-block-from-staff" class="{{ $defaultMode === 'from_staff' ? '' : 'hidden' }} border-b border-gray-100 bg-emerald-50/50 p-5 dark:border-[#374151] dark:bg-emerald-950/20">
                    <label for="staff_id" class="{{ $labelClass }}">{{ __('settings.users_pick_staff') }}</label>
                    <select name="staff_id" id="staff_id" class="{{ $inputClass }} @error('staff_id') border-red-300 @enderror">
                        <option value="">{{ __('settings.users_pick_staff_placeholder') }}</option>
                        @foreach ($linkableStaff as $member)
                            <option value="{{ $member->id }}" @selected((string) old('staff_id') === (string) $member->id)>
                                {{ $member->full_name }} — {{ $member->roleTypeLabel() }}@if(filled($member->email)) ({{ $member->email }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('staff_id')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    <p id="user-staff-preview" class="m-0 mt-2 hidden text-xs font-medium text-emerald-900 dark:text-emerald-200" aria-live="polite"></p>
                    @if ($linkableStaff->isEmpty())
                        <p class="m-0 mt-2 text-xs text-amber-800 dark:text-amber-200">{{ __('settings.users_no_staff_without_login') }}</p>
                    @endif
                </div>
            @endunless

            <div id="user-block-new" class="{{ $isEdit || $defaultMode === 'new' ? '' : 'hidden' }} grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div>
                    <label for="name" class="{{ $labelClass }}">{{ __('settings.users_th_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $isEdit ? $user->name : '') }}" @if($isEdit) required @endif
                        class="{{ $inputClass }} @error('name') border-red-300 ring-red-200 @enderror">
                    @error('name')<p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="{{ $labelClass }}">{{ __('settings.users_th_email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $isEdit ? $user->email : '') }}" autocomplete="username" @if($isEdit) required @endif
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
                <div id="user-doctor-onboarding-hint" class="md:col-span-2 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
                    <p class="m-0 font-semibold">{{ __('settings.users_doctor_onboarding_title') }}</p>
                    <p class="m-0 mt-1">{{ __('settings.users_doctor_onboarding_body') }}</p>
                    <a href="{{ route('doctors.onboarding.create') }}" data-no-spa class="mt-2 inline-flex font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]">{{ __('settings.users_doctor_onboarding_link') }} →</a>
                </div>
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

@unless($isEdit)
    @push('scripts')
        @vite('resources/js/user-create.js')
    @endpush
@endunless
