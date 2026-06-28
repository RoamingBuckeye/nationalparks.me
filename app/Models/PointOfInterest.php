<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Coordinates;
use App\Integrations\Nps\Enums\PoiKind;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PointOfInterest extends Model
{
    protected $table = 'points_of_interest';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'kind' => PoiKind::class,
            'tags' => 'array',
            'amenities' => 'array',
            'details' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_passport_stamp_location' => 'boolean',
            'last_synced_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function park(): BelongsTo
    {
        return $this->belongsTo(Park::class);
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
