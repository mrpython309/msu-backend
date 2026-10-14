<?php

namespace App;

enum WarrantyType: string
{
    case MANUFACTURER = 'manufacturer';
    case VENDOR       = 'vendor';
    case EXTENDED     = 'extended';
    case AMC          = 'amc';

    public function label(): string
    {
        return match ($this) {
            self::MANUFACTURER => 'Manufacturer Warranty',
            self::VENDOR       => 'Vendor Warranty',
            self::EXTENDED     => 'Extended Warranty',
            self::AMC          => 'AMC (Annual Maintenance Contract)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MANUFACTURER => 'primary',
            self::VENDOR       => 'info',
            self::EXTENDED     => 'warning',
            self::AMC          => 'success',
        };
    }
}
