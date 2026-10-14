<?php

namespace App\Enums\Procurement;

enum ItemType: string
{
    case Asset = 'asset';
    case Consumable = 'consumable';

    public function label(): string
    {
        return match ($this) {
            self::Asset => 'Asset',
            self::Consumable => 'Consumable',
        };
    }

    public function createsAssetRecords(): bool
    {
        return $this === self::Asset;
    }

    public static function tryFromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom(strtolower(trim((string) $value)));
    }

    public static function resolve(
        mixed $explicit = null,
        ?\App\Models\AssetCategory $category = null,
    ): self {
        return self::tryFromMixed($explicit)
            ?? self::tryFromMixed($category?->item_type)
            ?? self::Asset;
    }
}
