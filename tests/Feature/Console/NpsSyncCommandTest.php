<?php

declare(strict_types=1);

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Enums\NpsEntity;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Testing\FakeNpsClient;
use App\Models\Alert;
use App\Models\NpsSync;
use App\Models\Park;
use App\Models\PointOfInterest;
use Tests\Factories\Nps\AlertDataFactory;
use Tests\Factories\Nps\ParkDataFactory;
use Tests\Factories\Nps\PointOfInterestDataFactory;

beforeEach(function () {
    $this->app->instance(NpsClient::class, FakeNpsClient::make()
        ->withParks([ParkDataFactory::yellowstone(), ParkDataFactory::devilsTower()])
        ->withPointsOfInterest('yell', PoiKind::Place, [PointOfInterestDataFactory::oldFaithful()])
    );
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
    $this->app->instance(NpsClient::class, FakeNpsClient::make()
        ->withParks([ParkDataFactory::sequoiaKingsCanyon()])
    );

    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();

    expect(Park::where('park_code', 'sequ')->first())
        ->not->toBeNull()
        ->and(Park::where('park_code', 'sequ')->first()->nps_source_code)
        ->toBe('seki')
        ->and(Park::where('park_code', 'sequ')->first()->nps_source_id)
        ->toBe(ParkDataFactory::sequoiaKingsCanyon()->npsId)
        ->and(Park::where('park_code', 'kica')->first()->nps_source_code)
        ->toBe('seki')
        ->and(Park::where('park_code', 'seki')->exists())
        ->toBeFalse();
});

it('syncs alerts only for canonical parks, duplicating seki to sequ + kica, and prunes stale rows', function () {
    $this->app->instance(NpsClient::class, FakeNpsClient::make()
        ->withParks([ParkDataFactory::sequoiaKingsCanyon(), ParkDataFactory::devilsTower()])
        ->withAlerts([
            AlertDataFactory::closure(['id' => 'alert-seki', 'parkCode' => 'seki', 'title' => 'Highway closure', 'description' => 'Rockslide.']),
            AlertDataFactory::advisory(['id' => 'alert-deto', 'parkCode' => 'deto', 'category' => 'Information', 'title' => 'Climbing closure', 'description' => 'Falcon nesting.']),
        ])
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
    $this->app->instance(NpsClient::class, FakeNpsClient::make()
        ->withParks([ParkDataFactory::sequoiaKingsCanyon()])
        ->withPointsOfInterest('seki', PoiKind::Place, [
            PointOfInterestDataFactory::oldFaithful(['id' => 'poi-sherman', 'title' => 'General Sherman Tree', 'parkCode' => 'seki']),
        ])
    );

    $this->artisan('nps:sync')->assertSuccessful();

    expect(PointOfInterest::count())->toBe(2)
        ->and(PointOfInterest::with('park')->get()->pluck('park.park_code')->sort()->values()->all())
        ->toBe(['kica', 'sequ']);
});
