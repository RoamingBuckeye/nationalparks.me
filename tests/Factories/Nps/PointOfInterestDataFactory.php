<?php

declare(strict_types=1);

namespace Tests\Factories\Nps;

use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\PoiKind;

final class PointOfInterestDataFactory
{
    /** @param array<string, mixed> $overrides */
    public static function oldFaithful(array $overrides = []): PointOfInterestData
    {
        return PointOfInterestData::fromArray(array_replace([
            'id' => '6DD2F4BE-2B7E-498D-9617-OLDFAITHFUL1',
            'title' => 'Old Faithful',
            'url' => 'https://www.nps.gov/places/old-faithful.htm',
            'latitude' => '44.46',
            'longitude' => '-110.83',
            'bodyText' => '<p>Old Faithful is the most famous geyser in Yellowstone.</p>',
            'tags' => ['geyser', 'wildlife'],
            'amenities' => ['Restrooms', 'Parking'],
            'relatedParks' => [['parkCode' => 'yell', 'fullName' => 'Yellowstone NP', 'name' => 'Yellowstone', 'designation' => 'National Park', 'url' => '', 'states' => 'WY']],
            'isPassportStampLocation' => '1',
            'isOpenToPublic' => '1',
        ], $overrides), PoiKind::Place);
    }

    /** @param array<string, mixed> $overrides */
    public static function winterRanger(array $overrides = []): PointOfInterestData
    {
        return PointOfInterestData::fromArray(array_replace([
            'id' => '2F40F0AE-85BC-4669-A1F3-WINTERRANGER',
            'title' => 'Winter Ranger Talks',
            'url' => 'https://www.nps.gov/thingstodo/winter-ranger.htm',
            'shortDescription' => 'Join a ranger for a winter talk.',
            'longDescription' => '<p>Free, family-friendly winter programs.</p>',
            'duration' => '1 Hour',
            'activities' => [['id' => 'a-1', 'name' => 'Hiking']],
            'topics' => [['id' => 't-1', 'name' => 'Wildlife']],
            'season' => ['Winter'],
            'parkCode' => 'yell',
            'doFeesApply' => 'false',
        ], $overrides), PoiKind::ThingToDo);
    }

    /** @param array<string, mixed> $overrides */
    public static function albrightVisitorCenter(array $overrides = []): PointOfInterestData
    {
        return PointOfInterestData::fromArray(array_replace([
            'id' => '2010AE0C-1A5A-46EF-8B65-ALBRIGHTVC01',
            'name' => 'Albright Visitor Center',
            'parkCode' => 'yell',
            'description' => 'Park orientation, info, and bookstore.',
            'latitude' => '44.9763',
            'longitude' => '-110.6995',
            'amenities' => [['name' => 'Restrooms'], ['name' => 'Bookstore']],
            'isPassportStampLocation' => '1',
        ], $overrides), PoiKind::VisitorCenter);
    }

    /** @param array<string, mixed> $overrides */
    public static function mammothCampground(array $overrides = []): PointOfInterestData
    {
        return PointOfInterestData::fromArray(array_replace([
            'id' => '88CAA89D-3F95-4AB3-A3E9-MAMMOTHCMP01',
            'name' => 'Mammoth Campground',
            'parkCode' => 'yell',
            'description' => 'Open year-round.',
            'latitude' => '44.97',
            'longitude' => '-110.69',
            'numberOfSitesReservable' => '85',
            'numberOfSitesFirstComeFirstServe' => '0',
            'reservationUrl' => 'https://reserve.example.com/mammoth',
        ], $overrides), PoiKind::Campground);
    }

    /**
     * Minimal POI with sensible defaults.
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function make(array $overrides = [], PoiKind $kind = PoiKind::Place): PointOfInterestData
    {
        return PointOfInterestData::fromArray(array_replace([
            'id' => '00000000-0000-0000-0000-poi000000001',
            'title' => 'Test POI',
            'parkCode' => 'test',
            'latitude' => '0',
            'longitude' => '0',
        ], $overrides), $kind);
    }
}
