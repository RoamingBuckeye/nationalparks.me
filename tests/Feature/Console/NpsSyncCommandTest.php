<?php

declare(strict_types=1);

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\NpsEntity;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Testing\FakeNpsClient;
use App\Models\NpsSync;
use App\Models\Park;
use App\Models\PointOfInterest;

function buildFakeClient(): FakeNpsClient
{
    $yellowstone = ParkData::fromArray([
        'id' => 'park-yell-uuid', 'parkCode' => 'yell', 'name' => 'Yellowstone',
        'fullName' => 'Yellowstone NP', 'designation' => 'NP', 'description' => '',
        'latitude' => '44.6', 'longitude' => '-110.5', 'states' => 'WY', 'url' => '',
    ]);

    $place = PointOfInterestData::fromArray([
        'id' => 'place-1', 'title' => 'Old Faithful', 'parkCode' => 'yell',
        'latitude' => '44.46', 'longitude' => '-110.83',
    ], PoiKind::Place);

    return FakeNpsClient::make()
        ->withParks([$yellowstone])
        ->withPointsOfInterest('yell', PoiKind::Place, [$place]);
}

beforeEach(function () {
    $this->app->instance(NpsClient::class, buildFakeClient());
});

it('syncs parks and POIs end-to-end via the all entity', function () {
    $exitCode = $this->artisan('nps:sync')->run();

    expect($exitCode)->toBe(0)
        ->and(Park::count())->toBe(1)
        ->and(PointOfInterest::count())->toBe(1);
});

it('records an nps_syncs row per entity with succeeded_at and a count', function () {
    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();

    $sync = NpsSync::query()->where('entity', NpsEntity::Parks->value)->latest('id')->first();

    expect($sync)->not->toBeNull()
        ->and($sync->records_processed)->toBe(1)
        ->and($sync->succeeded_at)->not->toBeNull()
        ->and($sync->last_error)->toBeNull();
});

it('limits the sync to a single park via --park-code', function () {
    $this->artisan('nps:sync', ['entity' => 'parks', '--park-code' => 'yell'])->assertSuccessful();

    expect(Park::count())->toBe(1)
        ->and(NpsSync::where('park_code', 'yell')->where('entity', NpsEntity::Parks->value)->exists())->toBeTrue();
});

it('rejects an unknown entity argument', function () {
    $this->artisan('nps:sync', ['entity' => 'bogus'])->assertFailed();
});

it('fails to sync POIs when no parks have been synced yet', function () {
    $this->app->instance(NpsClient::class, FakeNpsClient::make()->withAmenities([
        AmenityData::fromArray(['id' => 'a-1', 'name' => 'Restrooms', 'categories' => ['Facilities']]),
    ]));

    $this->artisan('nps:sync', ['entity' => 'pois'])->assertFailed();
});
