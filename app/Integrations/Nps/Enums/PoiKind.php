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

    /**
     * Human-readable singular label for UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Place => 'Place',
            self::ThingToDo => 'Thing to do',
            self::VisitorCenter => 'Visitor center',
            self::Campground => 'Campground',
        };
    }

    /**
     * Human-readable plural label for UI display.
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Place => 'Places',
            self::ThingToDo => 'Things to do',
            self::VisitorCenter => 'Visitor centers',
            self::Campground => 'Campgrounds',
        };
    }
}
