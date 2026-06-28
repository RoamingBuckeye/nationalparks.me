<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;
use Stringable;

final readonly class Coordinates implements Stringable
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidArgumentException("Latitude {$latitude} is out of range [-90, 90]");
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new InvalidArgumentException("Longitude {$longitude} is out of range [-180, 180]");
        }
    }

    public static function tryFromStrings(?string $latitude, ?string $longitude): ?self
    {
        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return null;
        }

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return new self((float) $latitude, (float) $longitude);
    }

    public function distanceToKm(self $other): float
    {
        // Haversine formula.
        $earthRadiusKm = 6371.0;
        $latFromRad = deg2rad($this->latitude);
        $latToRad = deg2rad($other->latitude);
        $latDeltaRad = deg2rad($other->latitude - $this->latitude);
        $lonDeltaRad = deg2rad($other->longitude - $this->longitude);

        $a = sin($latDeltaRad / 2) ** 2
            + cos($latFromRad) * cos($latToRad) * sin($lonDeltaRad / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function distanceToMi(self $other): float
    {
        return $this->distanceToKm($other) * 0.621371;
    }

    public function __toString(): string
    {
        return sprintf('%.6f, %.6f', $this->latitude, $this->longitude);
    }
}
