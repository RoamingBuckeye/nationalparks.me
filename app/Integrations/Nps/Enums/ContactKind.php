<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

enum ContactKind: string
{
    case Phone = 'phone';
    case Email = 'email';
}
