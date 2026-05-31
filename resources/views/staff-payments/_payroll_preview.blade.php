@php
    $cur = \App\Support\ClinicSettings::current()->currency;
@endphp
<div class="md:col-span-2 rounded-lg border border-gray-100 bg-gray-50/80 px-4 py-3 text-sm dark:border-[#374151] dark:bg-[#111827]/60">
    <p class="m-0 font-semibold text-gray-800 dark:text-[#E5E7EB]">{{ __('payroll.preview_title') }}</p>
    <p class="mt-2 m-0 tabular-nums text-gray-600 dark:text-[#9CA3AF]">{{ __('payroll.preview_total_due_label') }} <span id="sp_total_due" class="font-semibold text-gray-900 dark:text-[#F3F4F6]">0.00</span>@if(filled($cur)) <span class="text-xs text-gray-400 dark:text-slate-500">{{ $cur }}</span>@endif</p>
    <p class="mt-1 m-0 tabular-nums text-gray-600 dark:text-[#9CA3AF]">{{ __('payroll.preview_remaining_label') }} <span id="sp_remaining" class="font-semibold text-gray-900 dark:text-[#F3F4F6]">0.00</span>@if(filled($cur)) <span class="text-xs text-gray-400 dark:text-slate-500">{{ $cur }}</span>@endif</p>
    <p class="mt-2 m-0 text-xs text-gray-500 dark:text-[#94A3B8]">{{ __('payroll.preview_hint') }}</p>
</div>
