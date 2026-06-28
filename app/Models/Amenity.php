<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Amenity extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
        ];
    }

    /** @return MorphToMany<PointOfInterest, $this> */
    public function pointsOfInterest(): MorphToMany
    {
        return $this->morphedByMany(PointOfInterest::class, 'amenitiable');
    }
}
