<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Domain\Coordinates;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Exposes a `coordinates` accessor for any model with `latitude`/`longitude`
 * columns, returning a `Coordinates` value object (or null when unset).
 *
 * @property float|null $latitude
 * @property float|null $longitude
 */
trait HasCoordinates
{
    /** @return Attribute<Coordinates|null, never> */
    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Coordinates => $this->latitude !== null && $this->longitude !== null
                ? new Coordinates((float) $this->latitude, (float) $this->longitude)
                : null,
        );
    }
}
