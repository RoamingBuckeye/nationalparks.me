<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Contracts;

use App\Integrations\Nps\Data\AlertData;
use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use Illuminate\Support\LazyCollection;

interface NpsClient
{
    /**
     * Stream parks. If $parkCodes is null, all parks are returned.
     *
     * @param  list<string>|null  $parkCodes
     * @return LazyCollection<int, ParkData>
     */
    public function parks(?array $parkCodes = null): LazyCollection;

    public function park(string $parkCode): ParkData;

    /** @return LazyCollection<int, PointOfInterestData> */
    public function places(string $parkCode): LazyCollection;

    /** @return LazyCollection<int, PointOfInterestData> */
    public function thingsToDo(string $parkCode): LazyCollection;

    /** @return LazyCollection<int, PointOfInterestData> */
    public function visitorCenters(string $parkCode): LazyCollection;

    /** @return LazyCollection<int, PointOfInterestData> */
    public function campgrounds(string $parkCode): LazyCollection;

    /** @return LazyCollection<int, AlertData> */
    public function alerts(?string $parkCode = null): LazyCollection;

    /** @return LazyCollection<int, AmenityData> */
    public function amenities(): LazyCollection;
}
