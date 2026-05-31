@php
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $stats = $supplierStats ?? ['total' => 0, 'active' => 0];
@endphp

@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1400px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.suppliers_title') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ __('inventory.suppliers_subtitle') }}</p>
    </div>

    <x-inventory.nav />

    <div class="mb-6 grid grid-cols-2 gap-3 sm:max-w-md">
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('inventory.stat_suppliers_total') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('inventory.stat_suppliers_active') }}</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-emerald-900 dark:text-emerald-200">{{ number_format($stats['active']) }}</p>
        </div>
    </div>

    @can('manage inventory')
        <div class="dash-section-group">
            <p class="dash-section-label m-0">{{ __('inventory.add_supplier_heading') }}</p>
            <form method="POST" action="{{ route('inventory.suppliers.store') }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
                @csrf
                <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
                    <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('inventory.btn_add_supplier') }}</h2>
                </div>
                <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-3 sm:p-5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('inventory.field_supplier') }}</label>
                        <input type="text" name="name" required class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.phone') }}</label>
                        <input type="text" name="phone" class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.email') }}</label>
                        <input type="email" name="email" class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]">
                    </div>
                </div>
                <div class="border-t border-gray-100 px-4 py-3 dark:border-[#374151] sm:px-5">
                    <button type="submit" class="inline-flex rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white dark:bg-[#3B82F6]">{{ __('inventory.btn_add_supplier') }}</button>
                </div>
            </form>
        </div>
    @endcan

    <div class="dash-section-group">
        <p class="dash-section-label m-0">{{ __('inventory.list_heading_suppliers') }}</p>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 dark:border-[#374151] dark:bg-[#111827]/90">
                            <th class="px-4 py-3 text-start font-bold">{{ __('inventory.field_supplier') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('common.phone') }}</th>
                            <th class="px-4 py-3 text-start font-bold">{{ __('common.email') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $s)
                            <tr class="border-b border-gray-50 hover:bg-slate-50/80 dark:border-[#374151]/60 dark:hover:bg-[#111827]/50">
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-[#F3F4F6]">{{ $s->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $s->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $s->email ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-14 text-center text-slate-500">{{ __('inventory.empty_suppliers') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($suppliers->hasPages())
                <div class="border-t px-4 py-3 dark:border-[#374151]">{{ $suppliers->links() }}</div>
            @endif
        </div>
    </div>
</div>
