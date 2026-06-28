<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Coordinates;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Park extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'states' => 'array',
            'activities' => 'array',
            'topics' => 'array',
            'operating_hours' => 'array',
            'entrance_fees' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'last_synced_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @return HasMany<PointOfInterest, $this> */
    public function pointsOfInterest(): HasMany
    {
        return $this->hasMany(PointOfInterest::class);
    }

    /** @return MorphMany<Image, $this> */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /** @return MorphToMany<Activity, $this> */
    public function activities(): MorphToMany
    {
        return $this->morphToMany(Activity::class, 'activatable');
    }

    /** @return MorphToMany<Topic, $this> */
    public function topics(): MorphToMany
    {
        return $this->morphToMany(Topic::class, 'topicable');
    }

    /**
     * For split parks (e.g. Sequoia/Kings Canyon both derived from `seki`),
     * the upstream NPS source is what API calls and POI ownership reference.
     * For self-sourced parks this equals park_code.
     */
    public function npsSourceCode(): string
    {
        return $this->nps_source_code ?? $this->park_code;
    }

    public function isSplitChild(): bool
    {
        return $this->nps_source_code !== null;
    }

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
