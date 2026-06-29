<?php

declare(strict_types=1);

use App\Integrations\Nps\Enums\AlertCategory;

it('orders categories by descending severity', function () {
    expect(AlertCategory::Danger->severity())
        ->toBeGreaterThan(AlertCategory::ParkClosure->severity())
        ->and(AlertCategory::ParkClosure->severity())
        ->toBeGreaterThan(AlertCategory::Caution->severity())
        ->and(AlertCategory::Caution->severity())
        ->toBeGreaterThan(AlertCategory::Information->severity());
});

it('exposes a human-readable label', function () {
    expect(AlertCategory::ParkClosure->label())->toBe('Park Closure')
        ->and(AlertCategory::Danger->label())->toBe('Danger');
});
