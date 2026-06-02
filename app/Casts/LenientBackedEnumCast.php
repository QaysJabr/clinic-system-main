<?php

namespace App\Casts;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Backed enum cast that tolerates unknown legacy DB values (maps to fallback).
 *
 * @implements CastsAttributes<BackedEnum|null, BackedEnum|string|null>
 */
final class LenientBackedEnumCast implements CastsAttributes
{
    /**
     * @param  class-string<BackedEnum>  $enumClass
     */
    public function __construct(
        private readonly string $enumClass,
        private readonly ?string $fallback = null,
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?BackedEnum
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value;
        }

        $resolved = $this->enumClass::tryFrom((string) $value);

        if ($resolved !== null) {
            return $resolved;
        }

        if ($this->fallback !== null) {
            return $this->enumClass::from($this->fallback);
        }

        return null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}
