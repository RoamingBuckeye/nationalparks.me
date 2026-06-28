<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

enum FeeKind: string
{
    case Entrance = 'entrance';
    case Pass = 'pass';
}
