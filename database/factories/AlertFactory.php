<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Integrations\Nps\Enums\AlertCategory;
use App\Models\Alert;
use App\Models\Park;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nps_id' => fake()->uuid(),
            'park_id' => Park::factory(),
            'park_code' => fake()->lexify('????'),
            'category' => fake()->randomElement(AlertCategory::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'last_indexed_at' => now(),
            'last_synced_at' => now(),
        ];
    }

    public function category(AlertCategory $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category' => $category,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'archived_at' => now(),
        ]);
    }
}
