<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Visit;
use App\Models\VisitPointOfInterest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $userId = $request->user()->id;

        $parksVisited = Visit::query()
            ->where('user_id', $userId)
            ->distinct()
            ->count('park_id');

        $poisChecked = VisitPointOfInterest::query()
            ->whereIn('visit_id', Visit::query()->select('id')->where('user_id', $userId))
            ->count();

        $photos = Photo::query()->where('uploaded_by_user_id', $userId)->count();

        $recentVisits = Visit::query()
            ->with('park:id,name')
            ->where('user_id', $userId)
            ->orderByDesc('started_at')
            ->limit(5)
            ->get()
            ->map(fn (Visit $visit): array => [
                'id' => $visit->id,
                'park_name' => $visit->park->name,
                'started_at' => $visit->started_at->toDateString(),
                'is_live' => $visit->isLive(),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'parks_visited' => $parksVisited,
                'pois_checked' => $poisChecked,
                'photos' => $photos,
            ],
            'recentVisits' => $recentVisits,
        ]);
    }
}
