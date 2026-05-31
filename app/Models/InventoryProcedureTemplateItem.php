<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryProcedureTemplateItem extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'inventory_procedure_template_id',
        'inventory_item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryProcedureTemplateItem $line): void {
            if ($line->clinic_id) {
                return;
            }
            $template = InventoryProcedureTemplate::query()
                ->withoutGlobalScopes()
                ->find($line->inventory_procedure_template_id);
            if ($template) {
                $line->clinic_id = $template->clinic_id;
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InventoryProcedureTemplate::class, 'inventory_procedure_template_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
