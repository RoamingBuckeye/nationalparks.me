<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Park;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointOfInterest>
 */
class PointOfInterestFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nps_id' => fake()->uuid(),
            'park_id' => Park::factory(),
            'kind' => PoiKind::Place,
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'url' => fake()->url(),
            'tags' => [],
            'amenities' => [],
            'is_passport_stamp_location' => false,
            'details' => [],
            'operating_hours' => [],
            'last_synced_at' => now(),
        ];
    }
}
