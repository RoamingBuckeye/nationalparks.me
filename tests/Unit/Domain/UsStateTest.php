<?php

declare(strict_types=1);

use App\Domain\UsState;

it('covers all 50 states + DC + 5 territories', function () {
    expect(count(UsState::cases()))->toBe(56);
});

it('builds from the 2-letter code', function () {
    expect(UsState::tryFrom('WY'))->toBe(UsState::Wyoming)
        ->and(UsState::tryFrom('CA'))->toBe(UsState::California)
        ->and(UsState::tryFrom('AS'))->toBe(UsState::AmericanSamoa)
        ->and(UsState::tryFrom('ZZ'))->toBeNull();
});

it('renders the full name with proper spacing', function () {
    expect(UsState::Wyoming->fullName())->toBe('Wyoming')
        ->and(UsState::NewHampshire->fullName())->toBe('New Hampshire')
        ->and(UsState::DistrictOfColumbia->fullName())->toBe('District of Columbia')
        ->and(UsState::UsVirginIslands->fullName())->toBe('U.S. Virgin Islands');
});
