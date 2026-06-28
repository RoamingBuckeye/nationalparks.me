<?php

declare(strict_types=1);

use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Testing\FakeNpsClient;

function fakeYellowstone(): ParkData
{
    return ParkData::fromArray([
        'id' => 'park-yell', 'parkCode' => 'yell', 'name' => 'Yellowstone', 'fullName' => 'Yellowstone NP',
        'designation' => 'National Park', 'description' => '', 'latitude' => '44.6', 'longitude' => '-110.5',
        'states' => 'WY', 'url' => '',
    ]);
}

it('returns preloaded parks via LazyCollection', function () {
    $client = FakeNpsClient::make()->withParks([fakeYellowstone()]);

    $parks = $client->parks()->all();

    expect($parks)->toHaveCount(1)->and($parks[0]->parkCode)->toBe('yell');
});

it('filters parks by parkCode list', function () {
    $client = FakeNpsClient::make()->withParks([fakeYellowstone()]);

    expect($client->parks(['grca'])->count())->toBe(0)
        ->and($client->parks(['yell'])->count())->toBe(1);
});

it('returns POIs by park and kind', function () {
    $poi = PointOfInterestData::fromArray(
        ['id' => 'p1', 'title' => 'Old Faithful', 'parkCode' => 'yell'],
        PoiKind::Place,
    );

    $client = FakeNpsClient::make()->withPointsOfInterest('yell', PoiKind::Place, [$poi]);

    expect($client->places('yell')->count())->toBe(1)
        ->and($client->places('grca')->count())->toBe(0)
        ->and($client->thingsToDo('yell')->count())->toBe(0);
});

it('preloads amenities as a global reference list', function () {
    $client = FakeNpsClient::make()->withAmenities([
        AmenityData::fromArray(['id' => 'a-1', 'name' => 'Restrooms', 'categories' => ['Facilities']]),
    ]);

    expect($client->amenities()->count())->toBe(1);
});
