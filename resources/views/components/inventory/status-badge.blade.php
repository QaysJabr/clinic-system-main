@props(['item'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold '.$item->statusBadgeClasses()]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $item->status === \App\Support\Inventory\InventoryItemStatus::ACTIVE ? 'bg-emerald-500' : ($item->status === \App\Support\Inventory\InventoryItemStatus::DISCONTINUED ? 'bg-rose-500' : 'bg-slate-400') }}" aria-hidden="true"></span>
    {{ $item->statusLabel() }}
</span>
