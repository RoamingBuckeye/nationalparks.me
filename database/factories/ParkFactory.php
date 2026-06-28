<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Park;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Park>
 */
class ParkFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'nps_id' => fake()->uuid(),
            'park_code' => Str::lower(Str::random(4)),
            'name' => $name,
            'full_name' => "{$name} National Park",
            'designation' => 'National Park',
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'states' => ['WY'],
            'last_synced_at' => now(),
        ];
    }
}
