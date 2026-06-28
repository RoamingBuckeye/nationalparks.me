<?php

declare(strict_types=1);

use App\Actions\Nps\UpsertAlert;
use App\Actions\Nps\UpsertPark;
use App\Integrations\Nps\Enums\AlertCategory;
use App\Models\Alert;
use Tests\Factories\Nps\AlertDataFactory;
use Tests\Factories\Nps\ParkDataFactory;

it('creates an alert attached to the matching park', function () {
    (new UpsertPark)(ParkDataFactory::yellowstone());

    $alert = (new UpsertAlert)(AlertDataFactory::closure());

    expect($alert)->toBeInstanceOf(Alert::class)
        ->and($alert->park->park_code)->toBe('yell')
        ->and($alert->category)->toBe(AlertCategory::ParkClosure)
        ->and($alert->title)->toBe('Lower Loop Closed')
        ->and($alert->last_indexed_at)->not->toBeNull();
});

it('returns null when the alert references an unknown park', function () {
    $result = (new UpsertAlert)(AlertDataFactory::closure(['parkCode' => 'unkw']));

    expect($result)->toBeNull()
        ->and(Alert::count())->toBe(0);
});

it('is idempotent on repeated upserts', function () {
    (new UpsertPark)(ParkDataFactory::yellowstone());
    $action = new UpsertAlert;

    $first = $action(AlertDataFactory::closure());
    $second = $action(AlertDataFactory::closure(['title' => 'Lower Loop — Reopened Tomorrow']));

    expect($second->id)->toBe($first->id)
        ->and($second->title)->toBe('Lower Loop — Reopened Tomorrow')
        ->and(Alert::count())->toBe(1);
});

it('attaches the same NPS alert to multiple parks via parkCodeOverride', function () {
    (new UpsertPark)(ParkDataFactory::yellowstone(['parkCode' => 'sequ', 'name' => 'Sequoia', 'fullName' => 'Sequoia National Park']));
    (new UpsertPark)(ParkDataFactory::yellowstone(['parkCode' => 'kica', 'name' => 'Kings Canyon', 'fullName' => 'Kings Canyon National Park']));

    $upsert = new UpsertAlert;
    $upstream = AlertDataFactory::closure(['parkCode' => 'seki']);

    $upsert($upstream, parkCodeOverride: 'sequ');
    $upsert($upstream, parkCodeOverride: 'kica');

    expect(Alert::count())->toBe(2)
        ->and(Alert::pluck('park_code')->sort()->values()->all())->toBe(['kica', 'sequ']);
});
