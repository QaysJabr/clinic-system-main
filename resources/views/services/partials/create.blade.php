@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="max-w-3xl mx-auto py-12" dir="rtl">
        <h1 class="text-2xl font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0 mb-6">إضافة خدمة</h1>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <form method="POST" action="{{ route('services.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">اسم الخدمة</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">السعر</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}" required
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="doctor_percentage" class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">نسبة الطبيب (اختياري)</label>
                    <input type="number" step="0.01" min="0" max="100" name="doctor_percentage" id="doctor_percentage" value="{{ old('doctor_percentage') }}"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    @error('doctor_percentage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-[#0F4C81] focus:ring-[#0F4C81]">
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">مفعّل في قوائم الفواتير</label>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('services.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-[#4B5563] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">إلغاء</a>
                    <button type="submit" class="rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0c3d66]">حفظ</button>
                </div>
            </form>
        </div>
    </div>
