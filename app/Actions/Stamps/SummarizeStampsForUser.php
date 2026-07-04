<?php

declare(strict_types=1);

namespace App\Actions\Stamps;

use App\Domain\UsState;
use App\Enums\StampCriteria;
use App\Models\Park;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserStamp;
use App\Models\Visit;
use Illuminate\Support\Collection;

/**
 * Build the per-user stamp collection view model: every active stamp with the
 * user's earned state, live progress toward it, and any vintage-edition label.
 * Membership is computed in-memory from one pass over the parks so the whole
 * page costs a handful of queries rather than one per stamp.
 */
class SummarizeStampsForUser
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function __invoke(User $user): Collection
    {
        /** @var Collection<int, int> $visitedParkIds */
        $visitedParkIds = Visit::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('park_id');

        $visitedCount = $visitedParkIds->count();

        // park id => list<string> of its state codes
        $parkStates = [];
        foreach (Park::query()->active()->get(['id', 'states']) as $park) {
            $parkStates[$park->id] = array_map(static fn (UsState $state): string => $state->value, $park->states);
        }

        /** @var Collection<int, UserStamp> $earned keyed by stamp_id */
        $earned = UserStamp::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('stamp_id');

        return Stamp::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Stamp $stamp): array => $this->present($stamp, $visitedParkIds, $visitedCount, $parkStates, $earned->get($stamp->id)))
            ->values();
    }

    /**
     * @param  Collection<int, int>  $visitedParkIds
     * @param  array<int, list<string>>  $parkStates
     * @return array<string, mixed>
     */
    protected function present(Stamp $stamp, Collection $visitedParkIds, int $visitedCount, array $parkStates, ?UserStamp $userStamp): array
    {
        [$progress, $required] = $this->progressFor($stamp, $visitedParkIds, $visitedCount, $parkStates);

        $isEarned = $userStamp !== null;

        $vintageYear = $isEarned
            && $stamp->members_changed_at !== null
            && $userStamp->earned_at->lessThan($stamp->members_changed_at)
                ? $userStamp->earned_at->year
                : null;

        return [
            'id' => $stamp->id,
            'slug' => $stamp->slug,
            'name' => $stamp->name,
            'description' => $stamp->description,
            'scene' => $stamp->scene,
            'accent_color' => $stamp->accent_color,
            'category' => $stamp->category,
            'earned' => $isEarned,
            'progress' => $progress,
            'required' => $required,
            'earned_at' => $userStamp?->earned_at?->toDateString(),
            'vintage_year' => $vintageYear,
        ];
    }

    /**
     * @param  Collection<int, int>  $visitedParkIds
     * @param  array<int, list<string>>  $parkStates
     * @return array{int, int} [progress, required]
     */
    protected function progressFor(Stamp $stamp, Collection $visitedParkIds, int $visitedCount, array $parkStates): array
    {
        if ($stamp->criteria_type === StampCriteria::ParkCount) {
            $required = $stamp->required_count ?? 0;

            return [min($visitedCount, $required), $required];
        }

        $targetCodes = match ($stamp->criteria_type) {
            StampCriteria::StateSet => $stamp->state_code !== null ? [$stamp->state_code] : [],
            default => $stamp->region?->stateCodes() ?? [],
        };

        $memberIds = [];
        foreach ($parkStates as $parkId => $codes) {
            if (array_intersect($codes, $targetCodes) !== []) {
                $memberIds[] = $parkId;
            }
        }

        $required = $stamp->required_count ?? count($memberIds);
        $progress = count(array_intersect($memberIds, $visitedParkIds->all()));

        return [min($progress, $required), $required];
    }
}
