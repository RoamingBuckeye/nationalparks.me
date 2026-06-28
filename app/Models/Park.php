<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Coordinates;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Park extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'states' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'last_synced_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function pointsOfInterest(): HasMany
    {
        return $this->hasMany(PointOfInterest::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Coordinates => $this->latitude !== null && $this->longitude !== null
                ? new Coordinates((float) $this->latitude, (float) $this->longitude)
                : null,
        );
    }
}
