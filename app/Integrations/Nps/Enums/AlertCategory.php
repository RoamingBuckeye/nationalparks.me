<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

enum AlertCategory: string
{
    case ParkClosure = 'Park Closure';
    case Information = 'Information';
    case Caution = 'Caution';
    case Danger = 'Danger';

    public static function tryFromLabel(?string $label): ?self
    {
        if ($label === null || $label === '') {
            return null;
        }

        return self::tryFrom($label);
    }

    /**
     * Human-readable label for UI display (the value already reads cleanly).
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Relative severity for ordering — higher is more urgent.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Danger => 4,
            self::ParkClosure => 3,
            self::Caution => 2,
            self::Information => 1,
        };
    }
}
