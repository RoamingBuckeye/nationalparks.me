<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Parks\SummarizePark;
use App\Models\Park;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * Map of all parks, pinned and colored by the user's visited state.
     */
    public function __invoke(Request $request, SummarizePark $summarizePark): Response
    {
        $parks = Park::query()
            ->active()
            ->withVisitStatsFor($request->user()->id)
            ->withClosureStatus()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get()
            ->map(fn (Park $park): array => [
                ...$summarizePark($park),
                'href' => route('parks.show', $park->id),
            ]);

        return Inertia::render('Map', [
            'parks' => $parks,
            'visitedCount' => $parks->where('visited', true)->count(),
            'totalCount' => $parks->count(),
        ]);
    }
}
