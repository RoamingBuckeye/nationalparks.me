<?php

declare(strict_types=1);

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Data\AlertData;
use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\NpsEntity;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Testing\FakeNpsClient;
use App\Models\Alert;
use App\Models\NpsSync;
use App\Models\Park;
use App\Models\PointOfInterest;

function buildFakeClient(): FakeNpsClient
{
    $yellowstone = ParkData::fromArray([
        'id' => 'park-yell-uuid', 'parkCode' => 'yell', 'name' => 'Yellowstone',
        'fullName' => 'Yellowstone NP', 'designation' => 'National Park', 'description' => '',
        'latitude' => '44.6', 'longitude' => '-110.5', 'states' => 'WY', 'url' => '',
    ]);

    $devilsTower = ParkData::fromArray([
        'id' => 'park-deto-uuid', 'parkCode' => 'deto', 'name' => 'Devils Tower',
        'fullName' => 'Devils Tower National Monument', 'designation' => 'National Monument', 'description' => '',
        'latitude' => '44.5', 'longitude' => '-104.7', 'states' => 'WY', 'url' => '',
    ]);

    $place = PointOfInterestData::fromArray([
        'id' => 'place-1', 'title' => 'Old Faithful', 'parkCode' => 'yell',
        'latitude' => '44.46', 'longitude' => '-110.83',
    ], PoiKind::Place);

    return FakeNpsClient::make()
        ->withParks([$yellowstone, $devilsTower])
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

it('filters out units that do not match the designation', function () {
    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();

    expect(Park::count())->toBe(1)
        ->and(Park::first()->park_code)->toBe('yell');
});

it('syncs every unit when --designation=all is passed', function () {
    $this->artisan('nps:sync', ['entity' => 'parks', '--designation' => 'all'])->assertSuccessful();

    expect(Park::count())->toBe(2);
});

it('limits POI sync to designation-matching parks only', function () {
    $this->artisan('nps:sync', ['entity' => 'parks', '--designation' => 'all'])->assertSuccessful();

    $this->artisan('nps:sync', ['entity' => 'pois'])->assertSuccessful();

    expect(PointOfInterest::count())->toBe(1)
        ->and(PointOfInterest::first()->park->park_code)->toBe('yell');
});

it('splits seki into sequ and kica when syncing parks', function () {
    $seki = ParkData::fromArray([
        'id' => 'park-seki-uuid', 'parkCode' => 'seki', 'name' => 'Sequoia & Kings Canyon',
        'fullName' => 'Sequoia & Kings Canyon National Parks', 'designation' => 'National Parks',
        'description' => 'Two parks managed as one.', 'latitude' => '36.7', 'longitude' => '-118.6',
        'states' => 'CA', 'url' => 'https://www.nps.gov/seki',
    ]);
    $this->app->instance(NpsClient::class, FakeNpsClient::make()->withParks([$seki]));

    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();

    expect(Park::where('park_code', 'sequ')->first())
        ->not->toBeNull()
        ->and(Park::where('park_code', 'sequ')->first()->nps_source_code)
        ->toBe('seki')
        ->and(Park::where('park_code', 'sequ')->first()->nps_source_id)
        ->toBe('park-seki-uuid')
        ->and(Park::where('park_code', 'kica')->first()->nps_source_code)
        ->toBe('seki')
        ->and(Park::where('park_code', 'seki')->exists())
        ->toBeFalse();
});

it('syncs alerts only for canonical parks, duplicating seki to sequ + kica, and prunes stale rows', function () {
    // Seed sequ + kica from a seki upstream so the alert codeMap covers seki → [sequ, kica].
    $seki = ParkData::fromArray([
        'id' => 'park-seki-uuid', 'parkCode' => 'seki', 'name' => 'Sequoia & Kings Canyon',
        'fullName' => 'Sequoia & Kings Canyon National Parks', 'designation' => 'National Parks',
        'description' => '', 'latitude' => '36.7', 'longitude' => '-118.6', 'states' => 'CA', 'url' => '',
    ]);
    $devilsTower = ParkData::fromArray([
        'id' => 'park-deto-uuid', 'parkCode' => 'deto', 'name' => 'Devils Tower',
        'fullName' => 'Devils Tower NM', 'designation' => 'National Monument', 'description' => '',
        'latitude' => '44.5', 'longitude' => '-104.7', 'states' => 'WY', 'url' => '',
    ]);

    $sekiAlert = AlertData::fromArray([
        'id' => 'alert-seki', 'parkCode' => 'seki', 'category' => 'Park Closure',
        'title' => 'Highway closure', 'description' => 'Rockslide.',
    ]);
    $detoAlert = AlertData::fromArray([
        'id' => 'alert-deto', 'parkCode' => 'deto', 'category' => 'Information',
        'title' => 'Climbing closure', 'description' => 'Falcon nesting.',
    ]);

    $this->app->instance(NpsClient::class, FakeNpsClient::make()
        ->withParks([$seki, $devilsTower])
        ->withAlerts([$sekiAlert, $detoAlert])
    );

    // Seed a stale alert that won't be in the upstream — should be pruned.
    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();
    Alert::create([
        'nps_id' => '11111111-1111-1111-1111-111111111111',
        'park_id' => Park::where('park_code', 'sequ')->value('id'),
        'park_code' => 'sequ',
        'category' => null,
        'title' => 'Old alert that no longer exists upstream',
        'last_synced_at' => now()->subDay(),
    ]);

    $this->artisan('nps:sync', ['entity' => 'alerts'])->assertSuccessful();

    expect(Alert::pluck('park_code')->sort()->values()->all())
        ->toBe(['kica', 'sequ']); // deto skipped (non-canonical); stale row pruned
});

it('routes seki POI fetches to both sequ and kica using nps_source_code', function () {
    $seki = ParkData::fromArray([
        'id' => 'park-seki-uuid', 'parkCode' => 'seki', 'name' => 'Sequoia & Kings Canyon',
        'fullName' => 'Sequoia & Kings Canyon National Parks', 'designation' => 'National Parks',
        'description' => '', 'latitude' => '36.7', 'longitude' => '-118.6', 'states' => 'CA', 'url' => '',
    ]);
    $generalSherman = PointOfInterestData::fromArray([
        'id' => 'poi-sherman', 'title' => 'General Sherman Tree', 'parkCode' => 'seki',
        'latitude' => '36.58', 'longitude' => '-118.75',
    ], PoiKind::Place);

    $this->app->instance(NpsClient::class, FakeNpsClient::make()
        ->withParks([$seki])
        ->withPointsOfInterest('seki', PoiKind::Place, [$generalSherman])
    );

    $this->artisan('nps:sync')->assertSuccessful();

    expect(PointOfInterest::count())->toBe(2)
        ->and(PointOfInterest::with('park')->get()->pluck('park.park_code')->sort()->values()->all())
        ->toBe(['kica', 'sequ']);
});
