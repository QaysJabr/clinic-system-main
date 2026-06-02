@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true); @endphp
<div class="mx-auto w-full max-w-[900px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('staff.index') }}" data-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('staff.back_to_list') }}</a>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:gap-4">
            <x-staff.avatar :name="$staff->full_name" size="lg" class="hidden sm:inline-flex" />
            <div class="min-w-0">
                <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('staff.page_edit') }}</h1>
                <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ $staff->full_name }} · {{ $staff->roleTypeLabel() }}</p>
            </div>
        </div>
    </div>

    <x-staff.nav active="list" />

    @can('manage staff payroll')
        <div class="mb-6 overflow-hidden rounded-xl border border-indigo-200/80 bg-indigo-50/40 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <p class="m-0 text-sm font-bold text-indigo-900 dark:text-indigo-200">{{ __('staff.compensation_card_title') }}</p>
                    <p class="m-0 mt-1 text-xs text-indigo-800/80 dark:text-indigo-300/80">
                        @if ($staff->compensationProfile)
                            {{ $staff->compensationProfile->compactBadge() }}
                        @else
                            {{ __('staff.compensation_card_missing') }}
                        @endif
                    </p>
                </div>
                @if ($staff->compensationProfile)
                    <a href="{{ route('staff-compensation-profiles.edit', $staff->compensationProfile) }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-lg border border-indigo-300 bg-white px-4 py-2 text-sm font-semibold text-indigo-900 shadow-sm hover:bg-indigo-50 dark:border-indigo-700 dark:bg-[#1F2937] dark:text-indigo-200 dark:hover:bg-indigo-950/40">{{ __('staff.compensation_card_edit') }}</a>
                @else
                    <a href="{{ route('staff-compensation-profiles.create', ['staff_id' => $staff->id]) }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 dark:bg-indigo-600">{{ __('staff.compensation_card_add') }}</a>
                @endif
            </div>
        </div>
    @endcan

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
        'staff' => $staff,
        'linkableUsers' => $linkableUsers,
        'includeDoctorRole' => $includeDoctorRole ?? true,
        'action' => route('staff.update', $staff),
        'method' => 'PUT',
    ])
</div>
