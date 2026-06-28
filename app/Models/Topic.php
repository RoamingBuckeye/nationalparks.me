<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Topic extends Model
{
    protected $guarded = [];

    /** @return MorphToMany<Park, $this> */
    public function parks(): MorphToMany
    {
        return $this->morphedByMany(Park::class, 'topicable');
    }

    /** @return MorphToMany<PointOfInterest, $this> */
    public function pointsOfInterest(): MorphToMany
    {
        return $this->morphedByMany(PointOfInterest::class, 'topicable');
    }
}
