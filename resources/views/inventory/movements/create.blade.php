<x-app-layout>
<div class="mx-auto max-w-[1400px] py-6" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
    @include('inventory.partials.nav')
    <h1 class="mb-2 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.movement_create_title') }}</h1>
    <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">{{ __('inventory.movements_subtitle') }}</p>
    <form method="POST" action="{{ route('inventory.movements.store') }}" class="space-y-4 rounded-xl border p-6 bg-white dark:bg-[#1F2937] dark:border-[#374151]">
        @csrf
        <div>
            <label class="block text-sm font-bold mb-1">{{ __('inventory.field_name') }}</label>
            <select name="inventory_item_id" required class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">@foreach($items as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">{{ __('common.type') }}</label>
            <select name="type" required class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">@foreach($typeLabels as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">{{ __('inventory.field_quantity') }}</label>
            <input type="number" step="0.001" name="quantity" required class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">{{ __('inventory.movement_correction') }}</label>
            <input type="number" step="0.001" name="target_quantity" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]">
        </div>
        <textarea name="notes" rows="2" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827] dark:border-[#374151]" placeholder="{{ __('inventory.field_notes') }}"></textarea>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-[#0F4C81] px-4 py-2 text-white font-bold">{{ __('common.save') }}</button>
            <a href="{{ route('inventory.movements.index') }}" data-spa class="rounded-lg border px-4 py-2 font-bold">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
</x-app-layout>
