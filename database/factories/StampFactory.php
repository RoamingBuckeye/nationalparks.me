<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PassportRegion;
use App\Enums\StampCriteria;
use App\Models\Stamp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Stamp>
 */
class StampFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->word().' '.fake()->word());

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 1_000_000),
            'name' => $name,
            'description' => fake()->sentence(),
            'criteria_type' => StampCriteria::ParkCount,
            'required_count' => 1,
            'state_code' => null,
            'region' => null,
            'scene' => null,
            'accent_color' => '#2F7D46',
            'category' => null,
            'sort_order' => 0,
            'is_active' => true,
            'members_changed_at' => null,
        ];
    }

    public function parkCount(int $count): static
    {
        return $this->state(fn (array $attributes): array => [
            'criteria_type' => StampCriteria::ParkCount,
            'required_count' => $count,
            'state_code' => null,
            'region' => null,
        ]);
    }

    public function stateSet(string $stateCode, ?int $requiredCount = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'criteria_type' => StampCriteria::StateSet,
            'state_code' => $stateCode,
            'required_count' => $requiredCount,
            'region' => null,
        ]);
    }

    public function regionSet(PassportRegion $region): static
    {
        return $this->state(fn (array $attributes): array => [
            'criteria_type' => StampCriteria::RegionSet,
            'region' => $region,
            'accent_color' => $region->color(),
            'required_count' => null,
            'state_code' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
