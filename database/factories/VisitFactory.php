<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Park;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $started = fake()->dateTimeBetween('-1 year', '-1 day');

        return [
            'user_id' => User::factory(),
            'park_id' => Park::factory(),
            'started_at' => $started,
            'ended_at' => (clone $started)->modify('+4 hours'),
            'notes' => null,
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes): array => [
            'started_at' => now(),
            'ended_at' => null,
        ]);
    }

    public function withJournal(?string $notes = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'notes' => $notes ?? fake()->paragraph(),
        ]);
    }
}
