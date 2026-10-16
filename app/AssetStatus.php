<?php

namespace App;

enum AssetStatus:string
{
    case AVAILABLE = 'available';
    case ASSIGNED = 'assigned';
    /** Legacy DB value — display as Transferred (in transit to recipient). */
    case IN_TRANSIT = 'in_transit';
    case TRANSFERRED = 'transferred';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case DISPOSED = 'disposed';
    // Optional: label for UI
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::ASSIGNED => 'Assigned',
            self::IN_TRANSIT => 'Transferred',
            self::TRANSFERRED => 'Transferred',
            self::UNDER_MAINTENANCE => 'Under Maintenance',
            self::DISPOSED => 'Disposed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::ASSIGNED => 'primary',
            self::IN_TRANSIT => 'warning',
            self::TRANSFERRED => 'warning',
            self::UNDER_MAINTENANCE => 'warning',
            self::DISPOSED => 'danger',
        };
    }

    /**
     * For status dropdowns: use {@see self::TRANSFERRED} only, not legacy {@see self::IN_TRANSIT}.
     *
     * @return list<self>
     */
    public static function casesForFilter(): array
    {
        $cases = array_values(array_filter(
            self::cases(),
            static fn (self $c) => $c !== self::IN_TRANSIT
        ));

        usort($cases, static function (self $a, self $b): int {
            $order = [
                'available' => 0,
                'assigned' => 1,
                'transferred' => 2,
                'under_maintenance' => 3,
                'disposed' => 4,
            ];

            return ($order[$a->value] ?? 99) <=> ($order[$b->value] ?? 99);
        });

        return $cases;
    }
}
