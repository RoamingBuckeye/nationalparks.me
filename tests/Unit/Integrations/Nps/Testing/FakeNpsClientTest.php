<?php

declare(strict_types=1);

use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Testing\FakeNpsClient;
use Tests\Factories\Nps\ParkDataFactory;
use Tests\Factories\Nps\PointOfInterestDataFactory;

it('returns preloaded parks via LazyCollection', function () {
    $client = FakeNpsClient::make()->withParks([ParkDataFactory::yellowstone()]);

    $parks = $client->parks()->all();

    expect($parks)->toHaveCount(1)->and($parks[0]->parkCode)->toBe('yell');
});

it('filters parks by parkCode list', function () {
    $client = FakeNpsClient::make()->withParks([ParkDataFactory::yellowstone()]);

    expect($client->parks(['grca'])->count())->toBe(0)
        ->and($client->parks(['yell'])->count())->toBe(1);
});

it('returns POIs by park and kind', function () {
    $client = FakeNpsClient::make()->withPointsOfInterest('yell', PoiKind::Place, [
        PointOfInterestDataFactory::oldFaithful(),
    ]);

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
