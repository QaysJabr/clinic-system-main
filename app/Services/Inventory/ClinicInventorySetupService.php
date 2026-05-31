<?php

namespace App\Services\Inventory;

use App\Models\ExpenseCategory;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;

/**
 * Seeds default categories, units, and finance category for any clinic type (idempotent).
 */
final class ClinicInventorySetupService
{
    /**
     * @return array{categories: int, units: int}
     */
    public function ensureDefaults(int $clinicId): array
    {
        $categories = 0;
        $units = 0;

        foreach ($this->defaultCategories() as $i => $name) {
            InventoryCategory::query()->firstOrCreate(
                ['clinic_id' => $clinicId, 'name' => $name],
                ['sort_order' => $i, 'is_active' => true],
            );
            $categories++;
        }

        foreach ($this->defaultUnits() as $code => $label) {
            InventoryUnit::query()->firstOrCreate(
                ['clinic_id' => $clinicId, 'code' => $code],
                ['name' => $label, 'is_active' => true],
            );
            $units++;
        }

        $this->ensureInventoryExpenseCategory($clinicId);

        return ['categories' => $categories, 'units' => $units];
    }

    public function ensureInventoryExpenseCategory(int $clinicId): int
    {
        $name = __('inventory.default_expense_category');

        $category = ExpenseCategory::withoutGlobalScopes()->firstOrCreate(
            [
                'clinic_id' => $clinicId,
                'name' => $name,
            ],
            [
                'description' => __('inventory.default_expense_category_hint'),
                'status' => 'active',
            ],
        );

        return (int) $category->id;
    }

    /**
     * Generic categories suitable for dental, medical, cosmetic, and specialist clinics.
     *
     * @return list<string>
     */
    private function defaultCategories(): array
    {
        return [
            __('inventory.default_cat_clinical'),
            __('inventory.default_cat_consumables'),
            __('inventory.default_cat_surgical'),
            __('inventory.default_cat_cleaning'),
            __('inventory.default_cat_medications'),
            __('inventory.default_cat_equipment'),
            __('inventory.default_cat_specialty'),
            __('inventory.default_cat_cosmetics'),
            __('inventory.default_cat_laboratory'),
            __('inventory.default_cat_office'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function defaultUnits(): array
    {
        return [
            'piece' => __('inventory.unit_piece'),
            'box' => __('inventory.unit_box'),
            'ml' => __('inventory.unit_ml'),
            'gram' => __('inventory.unit_gram'),
            'pack' => __('inventory.unit_pack'),
            'bottle' => __('inventory.unit_bottle'),
        ];
    }
}
