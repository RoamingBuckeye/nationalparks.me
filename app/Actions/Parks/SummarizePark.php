<?php

declare(strict_types=1);

namespace App\Actions\Parks;

use App\Domain\UsState;
use App\Models\Park;
use Illuminate\Support\Carbon;

class SummarizePark
{
    /**
     * Map a park (with `withVisitStatsFor` applied) to the shared summary
     * shape used by the parks list, map, and public shared pages.
     *
     * @return array<string, mixed>
     */
    public function __invoke(Park $park): array
    {
        return [
            'id' => $park->id,
            'park_code' => $park->park_code,
            'name' => $park->name,
            'designation' => $park->designation,
            'states' => array_map(fn (UsState $state): string => $state->value, $park->states),
            'latitude' => $park->latitude,
            'longitude' => $park->longitude,
            'visits_count' => (int) $park->visits_count,
            'visited' => (int) $park->visits_count > 0,
            'last_visited_at' => $park->last_visited_at !== null
                ? Carbon::parse($park->last_visited_at)->toDateString()
                : null,
        ];
    }
}
