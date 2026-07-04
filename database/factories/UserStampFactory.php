<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Stamp;
use App\Models\User;
use App\Models\UserStamp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStamp>
 */
class UserStampFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stamp_id' => Stamp::factory(),
            'earned_at' => now(),
        ];
    }
}
