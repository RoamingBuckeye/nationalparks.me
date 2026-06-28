<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $filename = fake()->word().'.jpg';

        return [
            'photoable_type' => Visit::class,
            'photoable_id' => Visit::factory(),
            'disk' => 'local',
            'path' => 'photos/'.fake()->uuid().'/'.$filename,
            'original_filename' => $filename,
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(50_000, 5_000_000),
            'taken_at' => fake()->dateTimeBetween('-1 year'),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'uploaded_by_user_id' => User::factory(),
        ];
    }

    public function withoutExif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'taken_at' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
