@props(['type', 'label'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold '.\App\Support\Inventory\InventoryMovementType::badgeClass($type)]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ \App\Support\Inventory\InventoryMovementType::dotColor($type) }}" aria-hidden="true"></span>
    {{ $label }}
</span>
