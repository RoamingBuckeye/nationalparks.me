<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The regions used by the NPS "Passport to Your National Parks" program, each
 * with its official passport color. National Capital is omitted — it has none
 * of the 63 National Parks.
 *
 * `stateCodes()` lists the park-bearing states/territories in each region; when
 * a new National Park appears in a state not listed here, add it (and its state
 * collection) at the same time.
 */
enum PassportRegion: string
{
    case NorthAtlantic = 'north_atlantic';
    case MidAtlantic = 'mid_atlantic';
    case Southeast = 'southeast';
    case Midwest = 'midwest';
    case Southwest = 'southwest';
    case RockyMountain = 'rocky_mountain';
    case West = 'west';
    case PacificNorthwestAlaska = 'pacific_northwest_alaska';

    public function label(): string
    {
        return match ($this) {
            self::NorthAtlantic => 'North Atlantic',
            self::MidAtlantic => 'Mid-Atlantic',
            self::Southeast => 'Southeast',
            self::Midwest => 'Midwest',
            self::Southwest => 'Southwest',
            self::RockyMountain => 'Rocky Mountain',
            self::West => 'West',
            self::PacificNorthwestAlaska => 'Pacific Northwest & Alaska',
        };
    }

    /**
     * The official Passport region color (used as the stamp's accent).
     */
    public function color(): string
    {
        return match ($this) {
            self::NorthAtlantic => '#8C6239',          // brown
            self::MidAtlantic => '#6FB7D4',            // light blue
            self::Southeast => '#7A5EA6',              // purple
            self::Midwest => '#E08A2E',                // orange
            self::Southwest => '#8A8E93',              // gray
            self::RockyMountain => '#E6B325',          // yellow
            self::West => '#2F7D46',                   // green
            self::PacificNorthwestAlaska => '#2170B0', // blue
        };
    }

    /**
     * Park-bearing state/territory codes in this region.
     *
     * @return list<string>
     */
    public function stateCodes(): array
    {
        return match ($this) {
            self::NorthAtlantic => ['ME'],
            self::MidAtlantic => ['VA', 'WV'],
            self::Southeast => ['KY', 'TN', 'NC', 'SC', 'FL', 'VI'],
            self::Midwest => ['OH', 'IN', 'MI', 'MN', 'MO', 'AR', 'ND', 'SD'],
            self::Southwest => ['TX', 'NM', 'AZ'],
            self::RockyMountain => ['UT', 'CO', 'MT', 'WY', 'ID'],
            self::West => ['CA', 'NV', 'HI', 'AS'],
            self::PacificNorthwestAlaska => ['WA', 'OR', 'AK'],
        };
    }
}
