<?php

declare(strict_types=1);

use App\Actions\Nps\UpsertPark;
use App\Domain\Coordinates;
use App\Integrations\Nps\Data\ParkData;
use App\Models\Park;

function parkData(array $overrides = []): ParkData
{
    return ParkData::fromArray(array_replace([
        'id' => 'park-yell-uuid',
        'parkCode' => 'yell',
        'name' => 'Yellowstone',
        'fullName' => 'Yellowstone National Park',
        'designation' => 'National Park',
        'description' => 'First NP.',
        'latitude' => '44.5982',
        'longitude' => '-110.5471',
        'states' => 'ID,MT,WY',
        'url' => 'https://www.nps.gov/yell',
        'images' => [
            ['url' => 'https://example.com/a.jpg', 'title' => 'A', 'altText' => 'a', 'caption' => '', 'credit' => 'NPS'],
        ],
    ], $overrides));
}

it('creates a park with images on first upsert', function () {
    $park = (new UpsertPark)(parkData());

    expect($park)->toBeInstanceOf(Park::class)
        ->and($park->park_code)->toBe('yell')
        ->and($park->states)->toBe(['ID', 'MT', 'WY'])
        ->and($park->images)->toHaveCount(1)
        ->and($park->coordinates)->toBeInstanceOf(Coordinates::class)
        ->and($park->last_synced_at)->not->toBeNull();
});

it('is idempotent on repeated upserts and reconciles images', function () {
    $action = new UpsertPark;
    $first = $action(parkData());

    $second = $action(parkData([
        'description' => 'Updated description.',
        'images' => [
            ['url' => 'https://example.com/a.jpg', 'title' => 'A renamed', 'altText' => '', 'caption' => '', 'credit' => 'NPS'],
            ['url' => 'https://example.com/b.jpg', 'title' => 'B', 'altText' => '', 'caption' => '', 'credit' => 'NPS'],
        ],
    ]));

    expect($second->id)->toBe($first->id)
        ->and($second->description)->toBe('Updated description.')
        ->and($second->images()->orderBy('sort_order')->pluck('url')->all())
        ->toBe(['https://example.com/a.jpg', 'https://example.com/b.jpg'])
        ->and(Park::count())->toBe(1);
});

it('clears archived_at when a park reappears in the upstream', function () {
    $park = (new UpsertPark)(parkData());
    $park->update(['archived_at' => now()]);

    (new UpsertPark)(parkData());

    expect($park->refresh()->archived_at)->toBeNull();
});

it('records nps_source_code and nps_source_id for split children', function () {
    $sequ = (new UpsertPark)(
        parkData(['parkCode' => 'sequ', 'name' => 'Sequoia', 'fullName' => 'Sequoia National Park']),
        sourceCode: 'seki',
        sourceId: 'park-seki-uuid',
    );

    expect($sequ->nps_source_code)->toBe('seki')
        ->and($sequ->nps_source_id)->toBe('park-seki-uuid')
        ->and($sequ->isSplitChild())->toBeTrue()
        ->and($sequ->npsSourceCode())->toBe('seki');
});

it('treats nps_source_code=null as self-sourced', function () {
    $park = (new UpsertPark)(parkData());

    expect($park->isSplitChild())->toBeFalse()
        ->and($park->npsSourceCode())->toBe('yell');
});

it('persists activities, topics, operating_hours, and entrance_fees as JSON', function () {
    $park = (new UpsertPark)(parkData([
        'activities' => [['id' => 'a-1', 'name' => 'Hiking'], ['id' => 'a-2', 'name' => 'Stargazing']],
        'topics' => [['id' => 't-1', 'name' => 'Geology']],
        'entranceFees' => [['cost' => '35.00', 'title' => 'Private Vehicle', 'description' => '7-day pass']],
        'entrancePasses' => [['cost' => '70.00', 'title' => 'Annual', 'description' => 'Annual pass']],
        'operatingHours' => [['name' => 'Park Hours', 'description' => 'Open year-round', 'standardHours' => ['monday' => 'All Day'], 'exceptions' => []]],
    ]));

    expect($park->activities)->toBe(['Hiking', 'Stargazing'])
        ->and($park->topics)->toBe(['Geology'])
        ->and($park->entrance_fees)->toHaveCount(2)
        ->and($park->entrance_fees[0])->toMatchArray(['kind' => 'entrance', 'title' => 'Private Vehicle', 'cost' => 35.0])
        ->and($park->entrance_fees[1])->toMatchArray(['kind' => 'pass', 'title' => 'Annual', 'cost' => 70.0])
        ->and($park->operating_hours)->toHaveCount(1)
        ->and($park->operating_hours[0]['name'])->toBe('Park Hours')
        ->and($park->operating_hours[0]['standard_hours'])->toBe(['monday' => 'All Day']);
});
