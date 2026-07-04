<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a stamp is earned.
 *
 * - ParkCount: visit any N distinct parks (milestones, the 63 Club).
 * - StateSet: visit the parks in a given state (all of them, or any N).
 * - RegionSet: visit the parks in a Passport region.
 *
 * State and region membership is derived live from `parks.states`, so these
 * never need a hand-maintained park list.
 */
enum StampCriteria: string
{
    case ParkCount = 'park_count';
    case StateSet = 'state_set';
    case RegionSet = 'region_set';
}
