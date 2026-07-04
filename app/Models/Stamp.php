<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PassportRegion;
use App\Enums\StampCriteria;
use Database\Factories\StampFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property StampCriteria $criteria_type
 * @property int|null $required_count
 * @property string|null $state_code
 * @property PassportRegion|null $region
 * @property string|null $scene
 * @property string|null $accent_color
 * @property string|null $category
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $members_changed_at
 */
class Stamp extends Model
{
    /** @use HasFactory<StampFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'criteria_type' => StampCriteria::class,
            'region' => PassportRegion::class,
            'is_active' => 'boolean',
            'members_changed_at' => 'datetime',
        ];
    }

    /** @param Builder<Stamp> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return HasMany<UserStamp, $this> */
    public function userStamps(): HasMany
    {
        return $this->hasMany(UserStamp::class);
    }

    /**
     * The parks that count toward this stamp, derived from its criteria. For
     * `park_count` this is every active park; for state/region it's the parks
     * whose `states` include the target state(s).
     *
     * @return Builder<Park>
     */
    public function memberParksQuery(): Builder
    {
        return match ($this->criteria_type) {
            StampCriteria::StateSet => Park::query()
                ->active()
                ->whereJsonContains('states', $this->state_code),
            StampCriteria::RegionSet => Park::query()
                ->active()
                ->where(function (Builder $query): void {
                    foreach ($this->region?->stateCodes() ?? [] as $code) {
                        $query->orWhereJsonContains('states', $code);
                    }
                }),
            StampCriteria::ParkCount => Park::query()->active(),
        };
    }

    /**
     * How many distinct parks are required to earn this stamp right now. For
     * set stamps a null `required_count` means "all current members".
     */
    public function requiredParkCount(): int
    {
        if ($this->criteria_type === StampCriteria::ParkCount) {
            return $this->required_count ?? 0;
        }

        return $this->required_count ?? $this->memberParksQuery()->count();
    }
}
