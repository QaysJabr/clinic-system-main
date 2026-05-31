<form method="post" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    @method('put')

    <div>
        <label for="update_password_current_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('profile.label_current_password') }}</label>
        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
            class="block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25 {{ $errors->updatePassword->has('current_password') ? 'border-red-300 dark:border-red-500/55' : 'border-slate-200 dark:border-[#4B5563]' }}" />
        @if ($errors->updatePassword->has('current_password'))
            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
        @endif
    </div>

    <div>
        <label for="update_password_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('profile.label_new_password') }}</label>
        <input id="update_password_password" name="password" type="password" autocomplete="new-password"
            class="block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25 {{ $errors->updatePassword->has('password') ? 'border-red-300 dark:border-red-500/55' : 'border-slate-200 dark:border-[#4B5563]' }}" />
        @if ($errors->updatePassword->has('password'))
            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $errors->updatePassword->first('password') }}</p>
        @endif
    </div>

    <div>
        <label for="update_password_password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('profile.label_confirm_password') }}</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
            class="block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25 {{ $errors->updatePassword->has('password_confirmation') ? 'border-red-300 dark:border-red-500/55' : 'border-slate-200 dark:border-[#4B5563]' }}" />
        @if ($errors->updatePassword->has('password_confirmation'))
            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ __('profile.btn_update_password') }}
        </button>
        @if (session('status') === 'password-updated')
            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)">{{ __('profile.toast_password_updated') }}</span>
        @endif
    </div>
</form>
