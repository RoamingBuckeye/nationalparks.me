<?php

declare(strict_types=1);

use App\Actions\Nps\UpsertAlert;
use App\Actions\Nps\UpsertPark;
use App\Integrations\Nps\Data\AlertData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Enums\AlertCategory;
use App\Models\Alert;
use App\Models\Park;

function seedPark(string $code = 'yell'): Park
{
    return (new UpsertPark)(ParkData::fromArray([
        'id' => "park-{$code}-uuid", 'parkCode' => $code, 'name' => 'Yellowstone',
        'fullName' => 'Yellowstone NP', 'designation' => 'National Park', 'description' => '',
        'latitude' => '44.6', 'longitude' => '-110.5', 'states' => 'WY', 'url' => '',
    ]));
}

function alertData(array $overrides = []): AlertData
{
    return AlertData::fromArray(array_replace([
        'id' => 'alert-1', 'parkCode' => 'yell', 'category' => 'Park Closure',
        'title' => 'Lower Loop Closed', 'description' => 'Avalanche risk.',
        'url' => 'https://www.nps.gov/yell/alert/1',
        'lastIndexedDate' => '2026-06-28T08:00:00Z',
    ], $overrides));
}

it('creates an alert attached to the matching park', function () {
    seedPark();

    $alert = (new UpsertAlert)(alertData());

    expect($alert)->toBeInstanceOf(Alert::class)
        ->and($alert->park->park_code)->toBe('yell')
        ->and($alert->category)->toBe(AlertCategory::ParkClosure)
        ->and($alert->title)->toBe('Lower Loop Closed')
        ->and($alert->last_indexed_at)->not->toBeNull();
});

it('returns null when the alert references an unknown park', function () {
    $result = (new UpsertAlert)(alertData(['parkCode' => 'unkw']));

    expect($result)->toBeNull()
        ->and(Alert::count())->toBe(0);
});

it('is idempotent on repeated upserts', function () {
    seedPark();
    $action = new UpsertAlert;

    $first = $action(alertData());
    $second = $action(alertData(['title' => 'Lower Loop — Reopened Tomorrow']));

    expect($second->id)->toBe($first->id)
        ->and($second->title)->toBe('Lower Loop — Reopened Tomorrow')
        ->and(Alert::count())->toBe(1);
});

it('attaches the same NPS alert to multiple parks via parkCodeOverride', function () {
    seedPark('sequ');
    seedPark('kica');

    $upsert = new UpsertAlert;
    $upstream = alertData(['parkCode' => 'seki']);

    $upsert($upstream, parkCodeOverride: 'sequ');
    $upsert($upstream, parkCodeOverride: 'kica');

    expect(Alert::count())->toBe(2)
        ->and(Alert::pluck('park_code')->sort()->values()->all())->toBe(['kica', 'sequ']);
});
