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
}
