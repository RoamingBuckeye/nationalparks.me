<?php

declare(strict_types=1);

use App\Integrations\Nps\Enums\ParkDesignation;

it('builds from the canonical 4 string designations', function () {
    expect(ParkDesignation::tryFrom('National Park'))->toBe(ParkDesignation::NationalPark)
        ->and(ParkDesignation::tryFrom('National Park & Preserve'))->toBe(ParkDesignation::NationalParkAndPreserve)
        ->and(ParkDesignation::tryFrom('National Parks'))->toBe(ParkDesignation::NationalParks)
        ->and(ParkDesignation::tryFrom('National and State Parks'))->toBe(ParkDesignation::NationalAndStateParks)
        ->and(ParkDesignation::tryFrom(''))->toBe(ParkDesignation::None);
});

it('returns null for non-canonical designations', function () {
    expect(ParkDesignation::tryFrom('National Monument'))->toBeNull()
        ->and(ParkDesignation::tryFrom('National Recreation Area'))->toBeNull();
});

it('flags the canonical cases via isCanonical', function () {
    expect(ParkDesignation::NationalPark->isCanonical())->toBeTrue()
        ->and(ParkDesignation::NationalParkAndPreserve->isCanonical())->toBeTrue()
        ->and(ParkDesignation::NationalAndStateParks->isCanonical())->toBeTrue()
        ->and(ParkDesignation::NationalParks->isCanonical())->toBeTrue() // seki — gets split
        ->and(ParkDesignation::None->isCanonical())->toBeFalse(); // npsa qualifies via allowlist
});
