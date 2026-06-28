<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Coordinates;
use App\Integrations\Nps\Enums\PoiKind;
use Database\Factories\PointOfInterestFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class PointOfInterest extends Model
{
    /** @use HasFactory<PointOfInterestFactory> */
    use HasFactory;

    protected $table = 'points_of_interest';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'kind' => PoiKind::class,
            'tags' => 'array',
            'amenities' => 'array',
            'details' => 'array',
            'operating_hours' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_passport_stamp_location' => 'boolean',
            'last_synced_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Park, $this> */
    public function park(): BelongsTo
    {
        return $this->belongsTo(Park::class);
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

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /** @return MorphToMany<Amenity, $this> */
    public function amenities(): MorphToMany
    {
        return $this->morphToMany(Amenity::class, 'amenitiable');
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
