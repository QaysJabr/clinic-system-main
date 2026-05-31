<x-app-layout>
@if(! empty($pageTitle ?? null))<span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>@endif
<div class="max-w-[960px] mx-auto" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
    @include('inventory.partials.nav')
    <h1 class="text-2xl font-bold text-[#0F4C81] dark:text-[#93C5FD] mb-2">{{ __('inventory.purchase_create_title') }}</h1>
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">{{ __('inventory.purchase_create_hint') }}</p>
    <form method="POST" action="{{ route('inventory.purchases.store') }}" class="space-y-6 rounded-xl border p-6 bg-white dark:bg-[#1F2937] dark:border-[#374151]">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-bold mb-1">{{ __('inventory.field_purchase_date') }}</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">{{ __('inventory.field_reference') }}</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold mb-1">{{ __('inventory.field_supplier') }}</label>
                <select name="inventory_supplier_id" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">
                    <option value="">—</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('inventory_supplier_id') == $s->id)>{{ $s->name }}</option>@endforeach
                </select>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 p-4 dark:border-[#374151]">
            <div class="flex justify-between items-center mb-3">
                <p class="font-bold m-0">{{ __('inventory.purchase_lines_title') }}</p>
                <button type="button" id="inv-add-line" class="text-sm font-bold text-[#0F4C81] hover:underline">{{ __('inventory.btn_add_line') }}</button>
            </div>
            <div id="inv-purchase-lines" class="space-y-3" data-line-template="purchase-line">
                <div class="purchase-line grid gap-2 sm:grid-cols-6 items-end border-b border-slate-100 pb-3 dark:border-[#374151]">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('inventory.field_name') }}</label>
                        <select name="lines[0][inventory_item_id]" required class="mt-1 w-full rounded-lg border px-2 py-2 text-sm dark:bg-[#111827] dark:border-[#374151]">
                            @foreach($items as $it)<option value="{{ $it->id }}">{{ $it->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">{{ __('inventory.field_quantity') }}</label>
                        <input type="number" step="0.001" min="0.001" name="lines[0][quantity]" required class="mt-1 w-full rounded-lg border px-2 py-2 text-sm dark:bg-[#111827] dark:border-[#374151]">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">{{ __('inventory.field_unit_cost') }}</label>
                        <input type="number" step="0.01" min="0" name="lines[0][unit_cost]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm dark:bg-[#111827] dark:border-[#374151]">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">{{ __('inventory.field_expiry') }}</label>
                        <input type="date" name="lines[0][expiry_date]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm dark:bg-[#111827] dark:border-[#374151]">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">{{ __('inventory.field_batch') }}</label>
                        <input type="text" name="lines[0][batch_number]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm dark:bg-[#111827] dark:border-[#374151]">
                    </div>
                </div>
            </div>
        </div>

        @can('manage expenses')
        <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <label class="inline-flex items-center gap-2 font-bold text-sm">
                <input type="checkbox" name="post_to_expense" value="1" @checked(old('post_to_expense', true))>
                {{ __('inventory.post_to_expense') }}
            </label>
            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 m-0">{{ __('inventory.post_to_expense_hint') }}</p>
            <div class="mt-3">
                <label class="block text-xs font-bold mb-1">{{ __('inventory.field_expense_category') }}</label>
                <select name="expense_category_id" class="w-full max-w-md rounded-lg border px-3 py-2 text-sm dark:bg-[#111827] dark:border-[#374151]">
                    <option value="">{{ __('inventory.expense_category_default') }}</option>
                    @foreach($expenseCategories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('expense_category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endcan

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white">{{ __('common.save') }}</button>
            <a href="{{ route('inventory.purchases.index') }}" data-spa class="rounded-lg border px-5 py-2.5 text-sm font-bold">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
</x-app-layout>
