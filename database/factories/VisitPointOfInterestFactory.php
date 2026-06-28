<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PointOfInterest;
use App\Models\Visit;
use App\Models\VisitPointOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitPointOfInterest>
 */
class VisitPointOfInterestFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'visit_id' => Visit::factory(),
            'point_of_interest_id' => PointOfInterest::factory(),
            'checked_at' => now(),
        ];
    }
}
