<?php

namespace App\Enums\Procurement;

enum IndentApprovalLevel: string
{
    case L1 = 'L1';
    case L2 = 'L2';
    case L3 = 'L3';

    public function number(): int
    {
        return match ($this) {
            self::L1 => 1,
            self::L2 => 2,
            self::L3 => 3,
        };
    }

    public static function fromNumber(int $level): self
    {
        return match ($level) {
            1 => self::L1,
            2 => self::L2,
            3 => self::L3,
            default => throw new \InvalidArgumentException("Unsupported approval level [{$level}]."),
        };
    }
}
