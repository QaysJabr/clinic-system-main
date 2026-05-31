@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="max-w-[1400px] mx-auto" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="mb-8">
            <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('payroll.runs_page_title') }}</h1>
            <p class="text-gray-600 dark:text-[#9CA3AF] text-sm mt-2 m-0">{!! __('payroll.runs_subtitle') !!}</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
                <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
                    <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('payroll.runs_flash_could_not_save') }}</p>
                </div>
                <ul class="m-0 list-none space-y-1.5 px-4 py-3 text-sm text-red-800 sm:px-5 dark:text-red-200">
                    @foreach ($errors->all() as $error)
                        <li class="flex gap-2"><span class="text-red-600 dark:text-red-400">•</span><span>{{ $error }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide mb-3 m-0">{{ __('payroll.runs_section_register_period') }}</p>
        <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90 px-4 py-4 sm:px-5">
                <h2 class="text-base font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('payroll.runs_heading_new_period') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <form method="POST" action="{{ route('payroll-runs.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-12 lg:items-end">
                    @csrf
                    <div class="lg:col-span-2">
                        <label for="period_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.runs_field_period_kind') }}</label>
                        <select name="period_type" id="period_type" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="weekly">{{ \App\Enums\PaymentCycle::Weekly->label() }}</option>
                            <option value="monthly">{{ \App\Enums\PaymentCycle::Monthly->label() }}</option>
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <label for="period_start" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.runs_field_period_from') }}</label>
                        <input type="date" name="period_start" id="period_start" value="{{ old('period_start') }}" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="period_end" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.runs_field_period_until') }}</label>
                        <input type="date" name="period_end" id="period_end" value="{{ old('period_end') }}" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="md:col-span-2 lg:col-span-4">
                        <label for="notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('payroll.runs_notes_optional') }}</label>
                        <input type="text" name="notes" id="notes" value="{{ old('notes') }}" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div class="lg:col-span-12 flex flex-wrap justify-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('payroll.runs_submit_register') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-xs font-bold text-gray-400 dark:text-[#94A3B8] uppercase tracking-wide mb-3 m-0">{{ __('payroll.runs_registered_periods_heading') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90 px-4 py-4 sm:px-5">
                <h2 class="text-base font-bold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('payroll.runs_payroll_cycles_table_heading') }}</h2>
                <a href="{{ route('staff-payments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('payroll.runs_link_staff_payments') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.runs_field_period_kind') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.runs_th_from') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.runs_th_until') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.runs_th_line_items') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.th_status') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('payroll.runs_th_notes') }}</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('payroll.runs_th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($runs as $run)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-[#111827]/80">
                                <td class="px-4 py-3 text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ $run->period_type->label() }}</td>
                                <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $run->period_start?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 tabular-nums text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $run->period_end?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 tabular-nums text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ $run->staff_payments_count }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200">{{ $run->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ \Illuminate\Support\Str::limit($run->notes ?? __('common.em_dash'), 48) }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <form method="POST" action="{{ route('payroll-runs.generate', $run) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-[#0F4C81] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F4C81] shadow-sm hover:bg-[#0F4C81]/5 dark:border-[#3B82F6] dark:bg-[#1F2937] dark:text-[#93C5FD] dark:hover:bg-[#111827]">{{ __('payroll.runs_generate_items') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('payroll.runs_empty_no_periods') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-4 sm:px-5">
                {{ $runs->links() }}
            </div>
        </div>
    </div>
