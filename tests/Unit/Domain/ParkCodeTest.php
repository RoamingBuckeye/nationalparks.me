<?php

declare(strict_types=1);

use App\Domain\ParkCode;

it('accepts valid 4-letter codes', function () {
    expect(new ParkCode('yell'))->toBeInstanceOf(ParkCode::class)
        ->and(new ParkCode('grca'))->toBeInstanceOf(ParkCode::class);
});

it('rejects invalid codes', function (string $bad) {
    new ParkCode($bad);
})->with([
    'too short' => 'yel',
    'too long' => 'yells',
    'uppercase' => 'YELL',
    'digits' => 'ye11',
    'empty' => '',
])->throws(InvalidArgumentException::class);

it('returns null from tryFrom for invalid values, instance for valid', function () {
    expect(ParkCode::tryFrom('YELL'))->toBeNull()
        ->and(ParkCode::tryFrom('yell'))->toBeInstanceOf(ParkCode::class);
});

it('compares by value with equals()', function () {
    expect((new ParkCode('yell'))->equals(new ParkCode('yell')))->toBeTrue()
        ->and((new ParkCode('yell'))->equals(new ParkCode('grca')))->toBeFalse();
});

it('renders as its string value', function () {
    expect((string) new ParkCode('yell'))->toBe('yell');
});
