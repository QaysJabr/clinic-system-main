@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="max-w-[1400px] mx-auto py-12" dir="rtl">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">كتالوج الخدمات</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-[#9CA3AF] m-0">أسعار موحّدة للفواتير — يمكن ربط كل بند بخدمة أو إدخال بند يدوي.</p>
            </div>
            <a href="{{ route('services.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">إضافة خدمة</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">الاسم</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">السعر</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">نسبة الطبيب %</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB]">الحالة</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-[#E5E7EB] w-[1%]">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-[#F3F4F6]">{{ $service->name }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ number_format((float) $service->price, 2) }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $service->doctor_percentage !== null ? number_format((float) $service->doctor_percentage, 2).'%' : '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($service->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">مفعّل</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">معطّل</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('services.edit', $service) }}" class="font-semibold text-[#1F7A8C] hover:underline">تعديل</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">لا توجد خدمات بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-4 dark:border-[#374151]">
                {{ $services->links() }}
            </div>
        </div>
    </div>
