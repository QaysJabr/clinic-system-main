@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('users.index') }}" data-spa class="mb-3 inline-flex text-sm font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('settings.users_back_to_list') }}</a>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('settings.users_create_heading') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('settings.users_create_intro') }}</p>
            <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('settings.users_create_intro_staff') }}</p>
        </div>
    </div>

    <x-settings.nav active="users" />

    @if ($errors->any())
        <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
            <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('settings.users_create_error_banner') }}</p>
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

    @include('users.partials.form', [
        'action' => route('users.store'),
        'roles' => $roles,
        'linkableStaff' => $linkableStaff ?? collect(),
        'defaultMode' => $defaultMode ?? 'from_staff',
    ])
</div>
