<?php

namespace App\Support\Inventory;

final class InventoryNameNormalizer
{
    public static function normalize(string $name): string
    {
        $normalized = mb_strtolower(trim($name));

        return preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
    }
}
