<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Testing;

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Data\AlertData;
use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Exceptions\NpsResponseException;
use Illuminate\Support\LazyCollection;

final class FakeNpsClient implements NpsClient
{
    /** @var list<ParkData> */
    protected array $parks = [];

    /** @var array<string, list<PointOfInterestData>> */
    protected array $poisByParkAndKind = [];

    /** @var list<AlertData> */
    protected array $alerts = [];

    /** @var list<AmenityData> */
    protected array $amenities = [];

    public static function make(): self
    {
        return new self;
    }

    /** @param list<ParkData> $parks */
    public function withParks(array $parks): self
    {
        $this->parks = $parks;

        return $this;
    }

    /** @param list<PointOfInterestData> $pois */
    public function withPointsOfInterest(string $parkCode, PoiKind $kind, array $pois): self
    {
        $this->poisByParkAndKind[$this->bucket($parkCode, $kind)] = $pois;

        return $this;
    }

    /** @param list<AlertData> $alerts */
    public function withAlerts(array $alerts): self
    {
        $this->alerts = $alerts;

        return $this;
    }

    /** @param list<AmenityData> $amenities */
    public function withAmenities(array $amenities): self
    {
        $this->amenities = $amenities;

        return $this;
    }

    public function parks(?array $parkCodes = null): LazyCollection
    {
        $parks = $parkCodes === null
            ? $this->parks
            : array_values(array_filter($this->parks, static fn (ParkData $p): bool => in_array($p->parkCode, $parkCodes, true)));

        return LazyCollection::make($parks);
    }

    public function park(string $parkCode): ParkData
    {
        foreach ($this->parks as $park) {
            if ($park->parkCode === $parkCode) {
                return $park;
            }
        }

        throw NpsResponseException::unexpectedShape('parks', "fake has no park '{$parkCode}'");
    }

    public function places(string $parkCode): LazyCollection
    {
        return $this->poisFor($parkCode, PoiKind::Place);
    }

    public function thingsToDo(string $parkCode): LazyCollection
    {
        return $this->poisFor($parkCode, PoiKind::ThingToDo);
    }

    public function visitorCenters(string $parkCode): LazyCollection
    {
        return $this->poisFor($parkCode, PoiKind::VisitorCenter);
    }

    public function campgrounds(string $parkCode): LazyCollection
    {
        return $this->poisFor($parkCode, PoiKind::Campground);
    }

    public function alerts(?string $parkCode = null): LazyCollection
    {
        $alerts = $parkCode === null
            ? $this->alerts
            : array_values(array_filter($this->alerts, static fn (AlertData $a): bool => $a->parkCode === $parkCode));

        return LazyCollection::make($alerts);
    }

    public function amenities(): LazyCollection
    {
        return LazyCollection::make($this->amenities);
    }

    /** @return LazyCollection<int, PointOfInterestData> */
    protected function poisFor(string $parkCode, PoiKind $kind): LazyCollection
    {
        return LazyCollection::make($this->poisByParkAndKind[$this->bucket($parkCode, $kind)] ?? []);
    }

    protected function bucket(string $parkCode, PoiKind $kind): string
    {
        return $parkCode.':'.$kind->value;
    }
}
