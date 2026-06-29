<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PointOfInterest;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;

class ToggleVisitPoiController extends Controller
{
    /**
     * Toggle a point of interest on a visit's checklist.
     *
     * Row exists ⇔ checked; toggling off deletes it. The POI must belong to
     * the same park as the visit.
     */
    public function __invoke(Visit $visit, PointOfInterest $pointOfInterest): RedirectResponse
    {
        $this->authorize('update', $visit);

        abort_unless($pointOfInterest->park_id === $visit->park_id, 422);

        $deleted = $visit->visitPois()
            ->where('point_of_interest_id', $pointOfInterest->id)
            ->delete();

        if ($deleted === 0) {
            $visit->visitPois()->create([
                'point_of_interest_id' => $pointOfInterest->id,
                'checked_at' => now(),
            ]);
        }

        return back();
    }
}
