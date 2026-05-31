@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $isSelf = auth()->id() === $user->id;
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('users.index') }}" data-spa class="mb-3 inline-flex text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('settings.users_back_to_list') }}</a>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('settings.users_edit_heading') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('settings.users_edit_intro') }}</p>
        </div>
    </div>

    <x-settings.nav active="users" />

    @if ($errors->any())
        <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
            <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('settings.users_edit_error_banner') }}</p>
            </div>
            <ul class="m-0 list-none space-y-1.5 px-4 py-3 text-sm text-red-800 sm:px-5 dark:text-red-200">
                @foreach ($errors->all() as $error)
                    <li class="flex gap-2">
                        <span class="text-red-600 dark:text-red-400" aria-hidden="true">•</span>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5 sm:py-5">
            <div class="flex items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="" class="h-14 w-14 shrink-0 rounded-2xl object-cover ring-2 ring-slate-100 dark:ring-slate-600" width="56" height="56">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="m-0 truncate text-lg font-bold text-gray-900 dark:text-[#F3F4F6]">{{ $user->name }}</p>
                        @if($isSelf)
                            <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-800 dark:bg-sky-950/45 dark:text-sky-300">{{ __('settings.users_you_badge') }}</span>
                        @endif
                    </div>
                    <p class="m-0 mt-1 truncate text-sm text-gray-500 dark:text-[#9CA3AF]">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if($user->roles->isNotEmpty())
                    <x-user.role-badge :role-name="$user->roles->first()->name" />
                @endif
                @if($user->created_at)
                    <span class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ __('settings.users_member_since') }} {{ $user->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
    </div>

    @include('users.partials.form', [
        'action' => route('users.update', $user),
        'user' => $user,
        'roles' => $roles,
    ])
</div>
