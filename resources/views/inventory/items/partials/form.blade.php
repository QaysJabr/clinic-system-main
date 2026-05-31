<form method="POST" action="{{ $item ? route('inventory.items.update', $item) : route('inventory.items.store') }}" class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
    @csrf
    @if($item) @method('PUT') @endif
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('inventory.field_name') }}</label>
        <input type="text" name="name" value="{{ old('name', $item?->name) }}" required class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_sku') }}</label>
            <input type="text" name="sku" value="{{ old('sku', $item?->sku) }}" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_status') }}</label>
            <select name="status" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
                @foreach($statusLabels as $code => $label)
                    <option value="{{ $code }}" @selected(old('status', $item?->status ?? 'active') === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_category') }}</label>
            <select name="inventory_category_id" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
                <option value="">—</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('inventory_category_id', $item?->inventory_category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_unit') }}</label>
            <select name="inventory_unit_id" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
                <option value="">—</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('inventory_unit_id', $item?->inventory_unit_id) == $unit->id)>{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_supplier') }}</label>
            <select name="inventory_supplier_id" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
                <option value="">—</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected(old('inventory_supplier_id', $item?->inventory_supplier_id) == $sup->id)>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_quantity') }}</label>
            <input type="number" step="0.001" min="0" name="quantity_on_hand" value="{{ old('quantity_on_hand', $item?->quantity_on_hand ?? 0) }}" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_minimum') }}</label>
            <input type="number" step="0.001" min="0" name="minimum_quantity" value="{{ old('minimum_quantity', $item?->minimum_quantity ?? 0) }}" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_expiry') }}</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date', $item?->expiry_date?->format('Y-m-d')) }}" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('inventory.field_unit_cost') }}</label>
            <input type="number" step="0.0001" min="0" name="unit_cost" value="{{ old('unit_cost', $item?->unit_cost) }}" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('inventory.field_notes') }}</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border px-3 py-2 dark:bg-[#111827]">{{ old('notes', $item?->notes) }}</textarea>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white">{{ __('common.save') }}</button>
        <a href="{{ route('inventory.items.index') }}" data-spa class="rounded-lg border px-5 py-2.5 text-sm font-bold">{{ __('common.cancel') }}</a>
    </div>
</form>
