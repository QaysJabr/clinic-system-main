@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $categoryStats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'used' => 0];
    $hasFilters = request()->filled('q') || request()->filled('status');
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('expenses.categories_page_title_index') }}</h1>
            <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('expenses.categories_subtitle_index') }}</p>
        </div>
        @can('manage expense categories')
            <a href="{{ route('expense-categories.create') }}" data-spa class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('expenses.categories_btn_add_short') }}</a>
        @endcan
    </div>

    <x-expense.nav active="categories" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-violet-200/90 bg-violet-50/50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('expenses.categories_stat_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-violet-900 dark:text-violet-200">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('expenses.categories_status_active') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('expenses.categories_status_inactive') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ number_format($stats['inactive']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/90 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">{{ __('expenses.categories_stat_used') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-amber-900 dark:text-amber-200">{{ number_format($stats['used']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('expense-categories.index', ['status' => \App\Models\ExpenseCategory::STATUS_ACTIVE]) }}" data-spa class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('status') === \App\Models\ExpenseCategory::STATUS_ACTIVE ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">
            <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
            {{ __('expenses.categories_quick_active') }}
        </a>
        <a href="{{ route('expense-categories.index', ['status' => \App\Models\ExpenseCategory::STATUS_INACTIVE]) }}" data-spa class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('status') === \App\Models\ExpenseCategory::STATUS_INACTIVE ? 'border-[#0F4C81] bg-[#0F4C81] text-white dark:bg-[#3B82F6]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300' }}">{{ __('expenses.categories_quick_inactive') }}</a>
        <a href="{{ route('expenses.index') }}" data-spa class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-300">{{ __('expenses.nav_list') }}</a>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('expenses.filter_results') }}</p>
        <form method="GET" action="{{ route('expense-categories.index') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.filter_search_criteria') }}</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
                    <div class="xl:col-span-5">
                        <label for="cat_q" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.search') }}</label>
                        <input type="search" name="q" id="cat_q" value="{{ request('q') }}" placeholder="{{ __('expenses.categories_search_placeholder') }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:placeholder:text-slate-500 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    </div>
                    <div class="xl:col-span-4">
                        <label for="cat_status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.categories_field_status') }}</label>
                        <select name="status" id="cat_status"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            <option value="">{{ __('expenses.filter_all') }}</option>
                            <option value="{{ \App\Models\ExpenseCategory::STATUS_ACTIVE }}" @selected(request('status') === \App\Models\ExpenseCategory::STATUS_ACTIVE)>{{ __('expenses.categories_status_active') }}</option>
                            <option value="{{ \App\Models\ExpenseCategory::STATUS_INACTIVE }}" @selected(request('status') === \App\Models\ExpenseCategory::STATUS_INACTIVE)>{{ __('expenses.categories_status_inactive') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2 xl:col-span-3 xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:bg-[#3B82F6]">{{ __('common.search') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('expense-categories.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('common.reset') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('expenses.categories_list_label') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                <div>
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('expenses.categories_table_heading') }}</h2>
                    @if ($categories->total() > 0)
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('expenses.results_count', ['from' => $categories->firstItem(), 'to' => $categories->lastItem(), 'total' => $categories->total()]) }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.categories_field_name') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.categories_field_description') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.categories_field_status') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.categories_th_expenses') }}</th>
                            <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.categories_th_created') }}</th>
                            <th class="px-4 py-3 text-end font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('expenses.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('expense-categories.edit', $category) }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ $category->name }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ $category->description ? \Illuminate\Support\Str::limit($category->description, 80) : __('common.em_dash') }}</td>
                                <td class="px-4 py-3 sm:px-5"><x-expense-category.status-badge :category="$category" /></td>
                                <td class="px-4 py-3 text-end tabular-nums font-semibold text-slate-700 dark:text-slate-300 sm:px-5">
                                    @if ($category->expenses_count > 0)
                                        <a href="{{ route('expenses.index', ['expense_category_id' => $category->id]) }}" data-spa class="text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ number_format($category->expenses_count) }}</a>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 tabular-nums dark:text-[#9CA3AF] sm:px-5">{{ $category->created_at?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-end whitespace-nowrap sm:px-5">
                                    <a href="{{ route('expense-categories.edit', $category) }}" data-spa class="text-sm font-semibold text-[#1F7A8C] hover:underline dark:text-[#5EEAD4]">{{ __('expenses.action_edit') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center sm:px-5">
                                    <p class="m-0 text-base font-semibold text-slate-600 dark:text-slate-300">{{ __('expenses.categories_empty_title') }}</p>
                                    <p class="m-0 mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('expenses.categories_no_rows') }}</p>
                                    @can('manage expense categories')
                                        <a href="{{ route('expense-categories.create') }}" data-spa class="mt-4 inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-bold text-white dark:bg-[#3B82F6]">{{ __('expenses.categories_btn_add_short') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="border-t border-gray-100 px-4 py-4 dark:border-[#374151] sm:px-5">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
</div>
