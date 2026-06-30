<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasCoordinates;
use App\Domain\Casts\UsStatesCast;
use App\Domain\UsState;
use App\Integrations\Nps\Enums\ParkDesignation;
use Database\Factories\ParkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $park_code
 * @property string|null $nps_source_code
 * @property string $name
 * @property string|null $full_name
 * @property string|null $designation
 * @property string|null $description
 * @property string|null $url
 * @property string|null $directions_url
 * @property string|null $weather_info
 * @property float|null $latitude
 * @property float|null $longitude
 * @property list<UsState> $states
 * @property Carbon|null $archived_at
 * @property-read int $visits_count
 * @property-read string|null $last_visited_at
 */
class Park extends Model
{
    /** @use HasFactory<ParkFactory> */
    use HasCoordinates, HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'states' => UsStatesCast::class,
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

    /**
     * The designation column stores the raw NPS string (NPS has 30+ values
     * across all park-system units). This accessor returns the matching
     * `ParkDesignation` case for the canonical set or null otherwise —
     * handy for filtering without locking the enum to be exhaustive.
     *
     * @return Attribute<ParkDesignation|null, never>
     */
    protected function designationEnum(): Attribute
    {
        return Attribute::make(
            get: fn (): ?ParkDesignation => ParkDesignation::tryFrom((string) $this->designation),
        );
    }

    /** @param Builder<Park> $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /**
     * Add per-park visit stats for a given user as `visits_count` and
     * `last_visited_at` selects (no eager-loaded rows, no N+1).
     *
     * @param  Builder<Park>  $query
     */
    public function scopeWithVisitStatsFor(Builder $query, int $userId): void
    {
        $query->addSelect([
            'visits_count' => Visit::query()
                ->selectRaw('count(*)')
                ->whereColumn('park_id', 'parks.id')
                ->where('user_id', $userId),
            'last_visited_at' => Visit::query()
                ->select('started_at')
                ->whereColumn('park_id', 'parks.id')
                ->where('user_id', $userId)
                ->latest('started_at')
                ->limit(1),
        ]);
    }

    /** @return HasMany<PointOfInterest, $this> */
    public function pointsOfInterest(): HasMany
    {
        return $this->hasMany(PointOfInterest::class);
    }

    /** @return HasMany<Visit, $this> */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /** @return HasMany<Alert, $this> */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
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
}
