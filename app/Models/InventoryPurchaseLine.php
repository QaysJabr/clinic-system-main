<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPurchaseLine extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'inventory_purchase_id',
        'inventory_item_id',
        'quantity',
        'unit_cost',
        'expiry_date',
        'batch_number',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'expiry_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryPurchaseLine $line): void {
            if ($line->clinic_id) {
                return;
            }
            $purchase = InventoryPurchase::query()
                ->withoutGlobalScopes()
                ->find($line->inventory_purchase_id);
            if ($purchase) {
                $line->clinic_id = $purchase->clinic_id;
            }
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(InventoryPurchase::class, 'inventory_purchase_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
