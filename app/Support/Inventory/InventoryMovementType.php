<?php

namespace App\Support\Inventory;

final class InventoryMovementType
{
    public const STOCK_IN = 'stock_in';

    public const STOCK_OUT = 'stock_out';

    public const ADJUSTMENT = 'adjustment';

    public const DAMAGED = 'damaged';

    public const EXPIRED = 'expired';

    public const CONSUMPTION = 'consumption';

    public const CORRECTION = 'correction';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::STOCK_IN,
            self::STOCK_OUT,
            self::ADJUSTMENT,
            self::DAMAGED,
            self::EXPIRED,
            self::CONSUMPTION,
            self::CORRECTION,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::STOCK_IN => __('inventory.movement_stock_in'),
            self::STOCK_OUT => __('inventory.movement_stock_out'),
            self::ADJUSTMENT => __('inventory.movement_adjustment'),
            self::DAMAGED => __('inventory.movement_damaged'),
            self::EXPIRED => __('inventory.movement_expired'),
            self::CONSUMPTION => __('inventory.movement_consumption'),
            self::CORRECTION => __('inventory.movement_correction'),
        ];
    }

    public static function increasesStock(string $type): bool
    {
        return in_array($type, [self::STOCK_IN, self::CORRECTION], true);
    }

    public static function isOutbound(string $type): bool
    {
        return in_array($type, [
            self::STOCK_OUT,
            self::DAMAGED,
            self::EXPIRED,
            self::CONSUMPTION,
        ], true);
    }

    public static function badgeClass(string $type): string
    {
        return match ($type) {
            self::STOCK_IN => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
            self::STOCK_OUT, self::CONSUMPTION => 'bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300',
            self::DAMAGED, self::EXPIRED => 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300',
            self::CORRECTION => 'bg-violet-100 text-violet-800 dark:bg-violet-950/50 dark:text-violet-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public static function dotColor(string $type): string
    {
        return match ($type) {
            self::STOCK_IN => 'bg-emerald-500',
            self::STOCK_OUT, self::CONSUMPTION => 'bg-sky-500',
            self::DAMAGED, self::EXPIRED => 'bg-red-500',
            self::CORRECTION => 'bg-violet-500',
            default => 'bg-slate-400',
        };
    }
}
