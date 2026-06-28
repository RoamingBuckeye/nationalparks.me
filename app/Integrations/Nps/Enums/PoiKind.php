<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

enum PoiKind: string
{
    case Place = 'place';
    case ThingToDo = 'thing_to_do';
    case VisitorCenter = 'visitor_center';
    case Campground = 'campground';

    public function npsEntity(): NpsEntity
    {
        return match ($this) {
            self::Place => NpsEntity::Places,
            self::ThingToDo => NpsEntity::ThingsToDo,
            self::VisitorCenter => NpsEntity::VisitorCenters,
            self::Campground => NpsEntity::Campgrounds,
        };
    }
}
