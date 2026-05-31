@php
    /** @var \App\Models\ExpenseCategory|null $category */
    $category = $category ?? null;
    $isEdit = $category !== null;
@endphp

@if ($errors->any())
    <div class="mb-6 overflow-hidden rounded-xl border border-red-200 bg-red-50/90 shadow-sm dark:border-red-900/50 dark:bg-red-950/30" role="alert">
        <div class="border-b border-red-100 bg-red-100/50 px-4 py-2.5 sm:px-5 dark:border-red-900/40">
            <p class="m-0 text-sm font-bold text-red-900 dark:text-red-200">{{ __('expenses.form_save_failed') }}</p>
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

<div class="dash-section-group">
    <p class="dash-section-label m-0">{{ __('expenses.categories_form_heading') }}</p>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <form
            action="{{ $isEdit ? route('expense-categories.update', $category) : route('expense-categories.store') }}"
            method="POST"
            class="space-y-0"
            novalidate
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.categories_field_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category?->name) }}" required
                        class="@error('name') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                    @error('name')
                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.categories_field_status') }}</label>
                    <select name="status" id="status" required
                        class="@error('status') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">
                        <option value="{{ \App\Models\ExpenseCategory::STATUS_ACTIVE }}" @selected(old('status', $category?->status ?? \App\Models\ExpenseCategory::STATUS_ACTIVE) === \App\Models\ExpenseCategory::STATUS_ACTIVE)>{{ __('expenses.categories_status_active') }}</option>
                        <option value="{{ \App\Models\ExpenseCategory::STATUS_INACTIVE }}" @selected(old('status', $category?->status) === \App\Models\ExpenseCategory::STATUS_INACTIVE)>{{ __('expenses.categories_status_inactive') }}</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('expenses.categories_field_description_optional') }}</label>
                    <textarea name="description" id="description" rows="3"
                        class="@error('description') border-red-300 ring-red-200 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25">{{ old('description', $category?->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 bg-gray-50/50 px-5 py-4 dark:border-[#374151] dark:bg-[#111827]/50 sm:px-6">
                <a href="{{ route('expense-categories.index') }}" data-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.back') }}</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
                    {{ $isEdit ? __('common.update') : __('common.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
