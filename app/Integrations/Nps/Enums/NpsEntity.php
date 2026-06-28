<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

enum NpsEntity: string
{
    case Parks = 'parks';
    case Places = 'places';
    case ThingsToDo = 'thingstodo';
    case VisitorCenters = 'visitorcenters';
    case Campgrounds = 'campgrounds';
    case Alerts = 'alerts';
    case Amenities = 'amenities';

    public function endpoint(): string
    {
        return $this->value;
    }
}
