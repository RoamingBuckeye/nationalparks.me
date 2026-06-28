<?php

declare(strict_types=1);

use App\Actions\Nps\UpsertPark;
use App\Actions\Nps\UpsertPointOfInterest;
use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Park;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Factories\Nps\ParkDataFactory;
use Tests\Factories\Nps\PointOfInterestDataFactory;

it('creates a POI linked to its park, with correct casts', function () {
    (new UpsertPark)(ParkDataFactory::yellowstone());

    $poi = (new UpsertPointOfInterest)(PointOfInterestDataFactory::oldFaithful());

    expect($poi)->toBeInstanceOf(PointOfInterest::class)
        ->and($poi->kind)->toBe(PoiKind::Place)
        ->and($poi->tags)->toBe(['geyser', 'wildlife'])
        ->and($poi->is_passport_stamp_location)->toBeTrue()
        ->and($poi->park->park_code)->toBe('yell');
});

it('throws when the parent park is not seeded yet', function () {
    (new UpsertPointOfInterest)(PointOfInterestDataFactory::oldFaithful(['parkCode' => 'grca']));
})->throws(ModelNotFoundException::class);

it('is idempotent on repeated upserts', function () {
    (new UpsertPark)(ParkDataFactory::yellowstone());
    $action = new UpsertPointOfInterest;

    $first = $action(PointOfInterestDataFactory::oldFaithful());
    $second = $action(PointOfInterestDataFactory::oldFaithful(['title' => 'Old Faithful Geyser']));

    expect($second->id)->toBe($first->id)
        ->and($second->title)->toBe('Old Faithful Geyser')
        ->and(PointOfInterest::count())->toBe(1);
});

it('attaches the same POI to multiple parks via parkCodeOverride (split scenario)', function () {
    // Two synthetic parks sharing one upstream NPS unit.
    (new UpsertPark)(ParkDataFactory::yellowstone(['parkCode' => 'sequ', 'name' => 'Sequoia', 'fullName' => 'Sequoia National Park']));
    (new UpsertPark)(ParkDataFactory::yellowstone(['parkCode' => 'kica', 'name' => 'Kings Canyon', 'fullName' => 'Kings Canyon National Park']));

    $upsert = new UpsertPointOfInterest;
    $sourceData = PointOfInterestDataFactory::oldFaithful(['parkCode' => 'seki']);

    $upsert($sourceData, parkCodeOverride: 'sequ');
    $upsert($sourceData, parkCodeOverride: 'kica');

    expect(PointOfInterest::count())->toBe(2)
        ->and(PointOfInterest::where('park_id', Park::where('park_code', 'sequ')->value('id'))->count())->toBe(1)
        ->and(PointOfInterest::where('park_id', Park::where('park_code', 'kica')->value('id'))->count())->toBe(1);
});
