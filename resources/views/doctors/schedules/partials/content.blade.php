@if (! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    $doctorDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
@endphp
<div class="mx-auto w-full max-w-[900px]" dir="{{ $doctorDir }}">
    <div class="mb-6">
        <a href="{{ route('doctors.index') }}" class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('doctors.back_to_list') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('schedules.title', ['doctor' => $doctor->full_name]) }}</h1>
        <p class="m-0 mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('doctors.schedules_subtitle') }}</p>
    </div>

    <x-doctor.nav :doctor="$doctor" active="schedules" />

    @if (session('success'))
        <p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200" role="status">{{ session('success') }}</p>
    @endif

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('schedules.add_block') }}</p>
        <form method="post" action="{{ route('doctors.schedules.store', $doctor) }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            @csrf
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('schedules.day') }}</label>
                    <select name="day_of_week" class="{{ $inputClass }}" required>
                        @foreach (range(0, 6) as $d)
                            <option value="{{ $d }}">{{ __('schedules.day_'.$d) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('schedules.start') }}</label>
                    <input type="time" name="start_time" class="{{ $inputClass }}" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('schedules.end') }}</label>
                    <input type="time" name="end_time" class="{{ $inputClass }}" required>
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]/30 dark:border-[#374151] dark:bg-[#111827]">
                        <span>{{ __('schedules.active') }}</span>
                    </label>
                </div>
            </div>
            <div class="border-t border-gray-100 bg-gray-50/80 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('doctors.section_schedules_list') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[480px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('schedules.day') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('schedules.start') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('schedules.end') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('schedules.active') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr class="border-b border-gray-100 dark:border-[#374151]">
                                <td class="px-4 py-3 font-medium sm:px-5">{{ __('schedules.day_'.$schedule->day_of_week) }}</td>
                                <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ substr((string) $schedule->start_time, 0, 5) }}</td>
                                <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400 sm:px-5">{{ substr((string) $schedule->end_time, 0, 5) }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $schedule->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $schedule->is_active ? __('common.yes') : __('common.no') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end sm:px-5">
                                    <form method="post" action="{{ route('doctors.schedules.destroy', [$doctor, $schedule]) }}" class="inline" data-confirm-title="{{ __('common.confirm_delete') }}" data-confirm="{{ __('common.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline dark:text-red-400">{{ __('common.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400 sm:px-5">{{ __('schedules.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
