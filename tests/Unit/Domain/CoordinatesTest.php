<?php

declare(strict_types=1);

use App\Domain\Coordinates;

it('constructs with valid lat and long', function () {
    $coord = new Coordinates(44.598, -110.547);

    expect($coord->latitude)->toBe(44.598)
        ->and($coord->longitude)->toBe(-110.547);
});

it('rejects out-of-range latitude', function () {
    new Coordinates(95.0, 0.0);
})->throws(InvalidArgumentException::class, 'Latitude');

it('rejects out-of-range longitude', function () {
    new Coordinates(0.0, -200.0);
})->throws(InvalidArgumentException::class, 'Longitude');

it('builds from strings, including NPS string-encoded numerics', function () {
    $coord = Coordinates::tryFromStrings('44.598', '-110.547');

    expect($coord)->not->toBeNull()
        ->and($coord->latitude)->toBe(44.598)
        ->and($coord->longitude)->toBe(-110.547);
});

it('returns null for empty or non-numeric input', function () {
    expect(Coordinates::tryFromStrings(null, '-110.5'))->toBeNull()
        ->and(Coordinates::tryFromStrings('', '-110.5'))->toBeNull()
        ->and(Coordinates::tryFromStrings('44.5', ''))->toBeNull()
        ->and(Coordinates::tryFromStrings('not a number', '0'))->toBeNull();
});

it('computes the haversine distance between Yellowstone and Grand Teton (~80km)', function () {
    $yellowstone = new Coordinates(44.598, -110.547);
    $grandTeton = new Coordinates(43.798, -110.681);

    $km = $yellowstone->distanceToKm($grandTeton);

    // Reference distance ~89 km.
    expect($km)->toBeGreaterThan(80.0)->and($km)->toBeLessThan(100.0);
});

it('renders as a formatted string', function () {
    expect((string) new Coordinates(44.598, -110.547))->toBe('44.598000, -110.547000');
});
