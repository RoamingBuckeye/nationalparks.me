<?php

declare(strict_types=1);

namespace App\Actions\Stamps;

use App\Enums\StampCriteria;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserStamp;
use App\Models\Visit;
use Illuminate\Support\Collection;

/**
 * Award any stamps a user newly qualifies for. Idempotent — already-earned
 * stamps are skipped, and earning is sticky (never revoked here). Returns the
 * stamps awarded in this run so the caller can reveal them.
 */
class EvaluateStamps
{
    /**
     * @return Collection<int, Stamp>
     */
    public function __invoke(User $user): Collection
    {
        /** @var Collection<int, int> $visitedParkIds distinct parks the user has visited */
        $visitedParkIds = Visit::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('park_id');

        $distinctCount = $visitedParkIds->count();

        $earnedStampIds = UserStamp::query()
            ->where('user_id', $user->id)
            ->pluck('stamp_id');

        $candidates = Stamp::query()
            ->active()
            ->whereNotIn('id', $earnedStampIds)
            ->get();

        $awarded = new Collection;

        foreach ($candidates as $stamp) {
            if (! $this->qualifies($stamp, $visitedParkIds, $distinctCount)) {
                continue;
            }

            UserStamp::query()->firstOrCreate(
                ['user_id' => $user->id, 'stamp_id' => $stamp->id],
                ['earned_at' => now()],
            );

            $awarded->push($stamp);
        }

        return $awarded;
    }

    /**
     * @param  Collection<int, int>  $visitedParkIds
     */
    protected function qualifies(Stamp $stamp, Collection $visitedParkIds, int $distinctCount): bool
    {
        return match ($stamp->criteria_type) {
            StampCriteria::ParkCount => $distinctCount >= ($stamp->required_count ?? PHP_INT_MAX),
            StampCriteria::StateSet, StampCriteria::RegionSet => $this->qualifiesForSet($stamp, $visitedParkIds),
        };
    }

    /**
     * @param  Collection<int, int>  $visitedParkIds
     */
    protected function qualifiesForSet(Stamp $stamp, Collection $visitedParkIds): bool
    {
        /** @var Collection<int, int> $memberIds */
        $memberIds = $stamp->memberParksQuery()->pluck('id');

        if ($memberIds->isEmpty()) {
            return false;
        }

        $required = $stamp->required_count ?? $memberIds->count();

        return $memberIds->intersect($visitedParkIds)->count() >= $required;
    }
}
