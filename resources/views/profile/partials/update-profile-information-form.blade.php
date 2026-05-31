<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('patch')

    <div
        class="flex flex-wrap items-start gap-4"
        x-data="{
            previewUrl: null,
            removeChosen: false,
            currentUrl: @js($user->avatarUrl()),
            defaultUrl: @js('https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0F4C81&color=fff&size=128'),
        }"
    >
        <img
            :src="previewUrl || (removeChosen ? defaultUrl : currentUrl)"
            alt=""
            class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-2 ring-slate-100 dark:ring-slate-600"
            width="80"
            height="80"
        />
        <div class="min-w-0 flex-1 space-y-2">
            <label for="avatar" class="mb-0 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('profile.label_avatar') }}</label>
            <input
                id="avatar"
                name="avatar"
                type="file"
                accept="image/jpeg,image/png,image/webp,image/gif"
                class="block w-full text-sm text-gray-600 dark:text-[#9CA3AF] file:me-3 file:rounded-lg file:border-0 file:bg-[#0F4C81] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0c3d66] {{ $errors->has('avatar') ? 'text-red-600' : '' }}"
                @change="
                    previewUrl = ($event.target.files && $event.target.files[0]) ? URL.createObjectURL($event.target.files[0]) : null;
                    removeChosen = false;
                "
            />
            <p class="text-xs text-gray-500 dark:text-[#9CA3AF] m-0">{{ __('profile.avatar_formats_hint') }}</p>
            @if ($errors->has('avatar'))
                <p class="mt-1 text-sm font-medium text-red-600">{{ $errors->first('avatar') }}</p>
            @endif
            @if ($user->avatar_path)
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-[#E5E7EB]">
                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]"
                        @change="removeChosen = $event.target.checked; if ($event.target.checked) previewUrl = null" />
                    {{ __('profile.remove_avatar_label') }}
                </label>
            @endif
        </div>
    </div>

    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('profile.label_name') }}</label>
        <input id="name" name="name" type="text" required autofocus autocomplete="name"
            value="{{ old('name', $user->name) }}"
            class="block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25 {{ $errors->has('name') ? 'border-red-300 dark:border-red-500/55' : 'border-slate-200 dark:border-[#4B5563]' }}" />
        @if ($errors->has('name'))
            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $errors->first('name') }}</p>
        @endif
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('profile.label_email') }}</label>
        <input id="email" name="email" type="email" required autocomplete="username"
            value="{{ old('email', $user->email) }}"
            class="block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25 {{ $errors->has('email') ? 'border-red-300 dark:border-red-500/55' : 'border-slate-200 dark:border-[#4B5563]' }}" />
        @if ($errors->has('email'))
            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $errors->first('email') }}</p>
        @endif

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-900 dark:border-amber-700/45 dark:bg-amber-950/35 dark:text-amber-100">
                <span>{{ __('profile.email_unverified') }}</span>
                <button type="submit" form="send-verification" class="me-1 font-semibold text-[#0F4C81] underline decoration-[#0F4C81]/30 hover:text-[#0c3d66] dark:text-[#93C5FD] dark:decoration-[#93C5FD]/30 dark:hover:text-white">
                    {{ __('profile.resend_verification') }}
                </button>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ __('profile.btn_save_changes') }}
        </button>
        @if (session('status') === 'profile-updated')
            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)">{{ __('profile.toast_profile_saved') }}</span>
        @endif
    </div>
</form>
