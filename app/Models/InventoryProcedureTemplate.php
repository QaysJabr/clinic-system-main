<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use App\Support\Inventory\InventoryNameNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryProcedureTemplate extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'name',
        'name_normalized',
        'description',
        'is_active',
        'auto_consume',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'auto_consume' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryProcedureTemplate $template): void {
            $template->name_normalized = InventoryNameNormalizer::normalize($template->name);
        });
    }

    /**
     * @return HasMany<InventoryProcedureTemplateItem, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(InventoryProcedureTemplateItem::class, 'inventory_procedure_template_id');
    }
}
