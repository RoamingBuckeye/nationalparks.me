<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Park;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Deterministic fixtures for the Playwright E2E suite: a verified test user
 * (test@example.com / password), one known park, and the full stamp catalog.
 * New River Gorge is West Virginia's only national park, so checking into it
 * completes both the "First Stamp" milestone and the "Mountaineer" collection.
 */
class E2eSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Park::factory()->create([
            'name' => 'New River Gorge',
            'full_name' => 'New River Gorge National Park',
            'park_code' => 'neri',
            'states' => ['WV'],
            'latitude' => 38.07,
            'longitude' => -81.08,
        ]);

        $this->call(StampSeeder::class);
    }
}
