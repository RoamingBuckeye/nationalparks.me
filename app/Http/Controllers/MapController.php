<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Park;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * Map of all parks, pinned and colored by the user's visited state.
     */
    public function __invoke(Request $request): Response
    {
        $parks = Park::query()
            ->active()
            ->withVisitStatsFor($request->user()->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get()
            ->map(fn (Park $park): array => [
                'id' => $park->id,
                'name' => $park->name,
                'latitude' => (float) $park->latitude,
                'longitude' => (float) $park->longitude,
                'visited' => (int) $park->visits_count > 0,
                'visits_count' => (int) $park->visits_count,
                'last_visited_at' => $park->last_visited_at !== null
                    ? Carbon::parse($park->last_visited_at)->toDateString()
                    : null,
                'href' => route('parks.show', $park->id),
            ]);

        return Inertia::render('Map', [
            'parks' => $parks,
            'visitedCount' => $parks->where('visited', true)->count(),
            'totalCount' => $parks->count(),
        ]);
    }
}
