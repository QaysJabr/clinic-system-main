<?php

namespace App\Support\Inventory;

final class InventoryItemStatus
{
    public const ACTIVE = 'active';

    public const INACTIVE = 'inactive';

    public const DISCONTINUED = 'discontinued';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::ACTIVE, self::INACTIVE, self::DISCONTINUED];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE => __('inventory.status_active'),
            self::INACTIVE => __('inventory.status_inactive'),
            self::DISCONTINUED => __('inventory.status_discontinued'),
        ];
    }
}
