<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use App\Support\Inventory\InventoryItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'inventory_category_id',
        'inventory_unit_id',
        'inventory_supplier_id',
        'name',
        'sku',
        'quantity_on_hand',
        'minimum_quantity',
        'status',
        'expiry_date',
        'batch_number',
        'unit_cost',
        'barcode',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'minimum_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'expiry_date' => 'date',
        ];
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity_on_hand <= (float) $this->minimum_quantity
            && (float) $this->minimum_quantity > 0;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if ($this->expiry_date === null) {
            return false;
        }

        return $this->expiry_date->isBetween(now()->startOfDay(), now()->addDays($days)->endOfDay());
    }

    public function stockBadgeClass(): string
    {
        if ($this->status !== InventoryItemStatus::ACTIVE) {
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        }
        if ($this->isExpired()) {
            return 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300';
        }
        if ($this->isLowStock()) {
            return 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-300';
        }

        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300';
    }

    public function statusLabel(): string
    {
        return InventoryItemStatus::labels()[$this->status] ?? $this->status;
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            InventoryItemStatus::ACTIVE => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
            InventoryItemStatus::INACTIVE => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            InventoryItemStatus::DISCONTINUED => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'inventory_unit_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(InventorySupplier::class, 'inventory_supplier_id');
    }

    /**
     * @return HasMany<InventoryStockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class, 'inventory_item_id')->latest('movement_at');
    }
}
