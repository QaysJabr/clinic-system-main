@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true); @endphp
<div class="mx-auto w-full max-w-[900px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('staff.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('staff.back_to_list') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('staff.page_create') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('staff.create_subtitle') }}</p>
    </div>

    <x-staff.nav active="list" />

    @if ($errors->any())
        <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
            <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('staff.error_save_header') }}</p>
            </div>
            <ul class="m-0 list-none space-y-1.5 px-4 py-3 text-sm text-red-800 sm:px-5 dark:text-red-200">
                @foreach ($errors->all() as $error)
                    <li class="flex gap-2"><span class="text-red-600 dark:text-red-400">•</span><span>{{ $error }}</span></li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('staff.partials.form', [
        'linkableUsers' => $linkableUsers,
        'defaultRoleType' => $defaultRoleType ?? null,
        'action' => route('staff.store'),
        'method' => 'POST',
    ])
</div>
