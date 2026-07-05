<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Parks\SummarizePark;
use App\Domain\UsState;
use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Alert;
use App\Models\Park;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParkController extends Controller
{
    /**
     * List all parks with the current user's visited state.
     */
    public function index(Request $request, SummarizePark $summarizePark): Response
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->string('search'));
        $visited = (string) $request->string('visited');
        $state = (string) $request->string('state');

        $parks = Park::query()
            ->active()
            ->withVisitStatsFor($userId)
            ->withClosureStatus()
            ->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $inner) => $inner
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('full_name', "%{$search}%"),
            ))
            ->when($state !== '', fn (Builder $query) => $query->whereJsonContains('states', $state))
            ->orderBy('name')
            ->get()
            ->when($visited === 'visited', fn ($parks) => $parks->where('visits_count', '>', 0))
            ->when($visited === 'unvisited', fn ($parks) => $parks->where('visits_count', 0))
            ->map($summarizePark)
            ->values();

        return Inertia::render('parks/Index', [
            'parks' => $parks,
            'filters' => [
                'search' => $search,
                'visited' => $visited,
                'state' => $state,
            ],
            'states' => $this->stateOptions(),
        ]);
    }

    /**
     * Show a single park: its profile, POI counts, and the user's visits.
     */
    public function show(Request $request, Park $park): Response
    {
        $poiCounts = $park->pointsOfInterest()
            ->active()
            ->toBase()
            ->selectRaw('kind, count(*) as aggregate')
            ->groupBy('kind')
            ->pluck('aggregate', 'kind');

        $alerts = $park->alerts()
            ->active()
            ->orderByDesc('last_indexed_at')
            ->get()
            ->sortByDesc(fn (Alert $alert): int => $alert->category?->severity() ?? 0)
            ->values()
            ->map(fn (Alert $alert): array => [
                'id' => $alert->id,
                'category' => $alert->category?->value,
                'severity' => $alert->category?->severity() ?? 0,
                'title' => $alert->title,
                'description' => $alert->description,
                'url' => $alert->url,
            ]);

        $visits = $park->visits()
            ->whereBelongsTo($request->user())
            ->withCount('visitPois')
            ->orderByDesc('started_at')
            ->get()
            ->map(fn (Visit $visit): array => [
                'id' => $visit->id,
                'started_at' => $visit->started_at->toDateString(),
                'ended_at' => $visit->ended_at?->toDateString(),
                'is_live' => $visit->isLive(),
                'pois_count' => (int) $visit->visit_pois_count,
                'has_notes' => $visit->notes !== null && $visit->notes !== '',
            ]);

        return Inertia::render('parks/Show', [
            'park' => [
                'id' => $park->id,
                'park_code' => $park->park_code,
                'name' => $park->name,
                'full_name' => $park->full_name,
                'designation' => $park->designation,
                'description' => $park->description,
                'url' => $park->url,
                'directions_url' => $park->directions_url,
                'weather_info' => $park->weather_info,
                'latitude' => $park->latitude,
                'longitude' => $park->longitude,
                'states' => array_map(
                    fn (UsState $state): array => ['code' => $state->value, 'name' => $state->fullName()],
                    $park->states,
                ),
            ],
            'poiCounts' => collect(PoiKind::cases())->map(fn (PoiKind $kind): array => [
                'value' => $kind->value,
                'label' => $kind->pluralLabel(),
                'count' => (int) ($poiCounts[$kind->value] ?? 0),
            ]),
            'alerts' => $alerts,
            'visits' => $visits,
        ]);
    }

    /**
     * Distinct states across active parks, for the index filter.
     *
     * @return array<int, array{code: string, name: string}>
     */
    protected function stateOptions(): array
    {
        return Park::query()
            ->active()
            ->get(['states'])
            ->flatMap(fn (Park $park): array => $park->states)
            ->unique(fn (UsState $state): string => $state->value)
            ->sortBy(fn (UsState $state): string => $state->fullName())
            ->map(fn (UsState $state): array => ['code' => $state->value, 'name' => $state->fullName()])
            ->values()
            ->all();
    }
}
