<?php

namespace App;

enum AssetOperationalStatus:string
{
    case WORKING = 'working';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case DAMAGED = 'damaged';
    case DISPOSED = 'disposed';

    // Optional: label for UI
    public function label(): string
    {
        return match ($this) {
            self::WORKING => 'Working',
            self::UNDER_MAINTENANCE => 'Under Maintenance',
            self::DAMAGED => 'Damaged',
            self::DISPOSED => 'Disposed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WORKING => 'success',
            self::UNDER_MAINTENANCE => 'warning',
            self::DAMAGED => 'danger',
            self::DISPOSED => 'danger',
        };
    }
}
