<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

enum AddressType: string
{
    case Physical = 'Physical';
    case Mailing = 'Mailing';
}
