<?php

declare(strict_types=1);

use App\Actions\Nps\UpsertPark;
use App\Actions\Nps\UpsertPointOfInterest;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Park;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

function seedYellowstone(): void
{
    (new UpsertPark)(ParkData::fromArray([
        'id' => 'park-yell-uuid',
        'parkCode' => 'yell',
        'name' => 'Yellowstone',
        'fullName' => 'Yellowstone NP',
        'designation' => 'NP',
        'description' => '',
        'latitude' => '44.6',
        'longitude' => '-110.5',
        'states' => 'WY',
        'url' => '',
    ]));
}

function poiData(array $overrides = [], PoiKind $kind = PoiKind::Place): PointOfInterestData
{
    return PointOfInterestData::fromArray(array_replace([
        'id' => 'poi-1',
        'title' => 'Old Faithful',
        'parkCode' => 'yell',
        'latitude' => '44.46',
        'longitude' => '-110.83',
        'bodyText' => '<p>Geyser.</p>',
        'tags' => ['geyser'],
        'amenities' => ['Parking'],
        'isPassportStampLocation' => '0',
    ], $overrides), $kind);
}

it('creates a POI linked to its park, with correct casts', function () {
    seedYellowstone();

    $poi = (new UpsertPointOfInterest)(poiData());

    expect($poi)->toBeInstanceOf(PointOfInterest::class)
        ->and($poi->kind)->toBe(PoiKind::Place)
        ->and($poi->tags)->toBe(['geyser'])
        ->and($poi->is_passport_stamp_location)->toBeFalse()
        ->and($poi->park->park_code)->toBe('yell');
});

it('throws when the parent park is not seeded yet', function () {
    (new UpsertPointOfInterest)(poiData(['parkCode' => 'grca']));
})->throws(ModelNotFoundException::class);

it('is idempotent on repeated upserts', function () {
    seedYellowstone();
    $action = new UpsertPointOfInterest;

    $first = $action(poiData());
    $second = $action(poiData(['title' => 'Old Faithful Geyser']));

    expect($second->id)->toBe($first->id)
        ->and($second->title)->toBe('Old Faithful Geyser')
        ->and(PointOfInterest::count())->toBe(1);
});

it('attaches the same POI to multiple parks via parkCodeOverride (split scenario)', function () {
    // Two synthetic parks sharing one upstream NPS unit.
    (new UpsertPark)(ParkData::fromArray([
        'id' => 'park-seki-uuid', 'parkCode' => 'sequ', 'name' => 'Sequoia',
        'fullName' => 'Sequoia National Park', 'designation' => 'National Park', 'description' => '',
        'latitude' => '36.6', 'longitude' => '-118.7', 'states' => 'CA', 'url' => '',
    ]));
    (new UpsertPark)(ParkData::fromArray([
        'id' => 'park-seki-uuid', 'parkCode' => 'kica', 'name' => 'Kings Canyon',
        'fullName' => 'Kings Canyon National Park', 'designation' => 'National Park', 'description' => '',
        'latitude' => '36.8', 'longitude' => '-118.5', 'states' => 'CA', 'url' => '',
    ]));

    $upsert = new UpsertPointOfInterest;
    $sourceData = poiData(['parkCode' => 'seki']);

    $upsert($sourceData, parkCodeOverride: 'sequ');
    $upsert($sourceData, parkCodeOverride: 'kica');

    expect(PointOfInterest::count())->toBe(2)
        ->and(PointOfInterest::where('park_id', Park::where('park_code', 'sequ')->value('id'))->count())->toBe(1)
        ->and(PointOfInterest::where('park_id', Park::where('park_code', 'kica')->value('id'))->count())->toBe(1);
});
