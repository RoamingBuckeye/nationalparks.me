<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Photo;
use App\Models\PointOfInterest;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    /**
     * Log a new visit — live check-in (no dates) or a backdated past visit.
     */
    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $visit = $request->user()->visits()->create([
            'park_id' => $data['park_id'],
            'started_at' => $data['started_at'] ?? now(),
            'ended_at' => $data['ended_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return to_route('visits.show', $visit);
    }

    /**
     * Show a visit: its details plus the park's paginated POI checklist.
     */
    public function show(Request $request, Visit $visit): Response
    {
        $this->authorize('view', $visit);

        $visit->load('park');

        $kind = (string) $request->string('kind');
        $search = trim((string) $request->string('search'));

        $pois = $visit->park->pointsOfInterest()
            ->active()
            ->when($kind !== '', fn (Builder $query) => $query->where('kind', $kind))
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('title')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (PointOfInterest $poi): array => [
                'id' => $poi->id,
                'title' => $poi->title,
                'kind' => $poi->kind->value,
                'kind_label' => $poi->kind->label(),
            ]);

        return Inertia::render('visits/Show', [
            'visit' => [
                'id' => $visit->id,
                'started_at' => $visit->started_at->toDateString(),
                'ended_at' => $visit->ended_at?->toDateString(),
                'is_live' => $visit->isLive(),
                'notes' => $visit->notes,
            ],
            'park' => [
                'id' => $visit->park->id,
                'name' => $visit->park->name,
            ],
            'pois' => $pois,
            'checkedPoiIds' => $visit->visitPois()->pluck('point_of_interest_id'),
            'photos' => $visit->photos()->latest()->get()->map(fn (Photo $photo): array => [
                'id' => $photo->id,
                'url' => route('photos.show', $photo->id),
                'original_filename' => $photo->original_filename,
                'taken_at' => $photo->taken_at?->toDateString(),
            ]),
            'totalPois' => $visit->park->pointsOfInterest()->active()->count(),
            'kinds' => collect(PoiKind::cases())->map(fn (PoiKind $poiKind): array => [
                'value' => $poiKind->value,
                'label' => $poiKind->pluralLabel(),
            ]),
            'filters' => [
                'kind' => $kind,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Update a visit's dates and Journal, or end a live visit.
     */
    public function update(UpdateVisitRequest $request, Visit $visit): RedirectResponse
    {
        $visit->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Visit updated.')]);

        return back();
    }

    /**
     * Delete a visit (its checked POIs cascade away).
     */
    public function destroy(Visit $visit): RedirectResponse
    {
        $this->authorize('delete', $visit);

        $parkId = $visit->park_id;

        $visit->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Visit deleted.')]);

        return to_route('parks.show', $parkId);
    }
}
