<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Parks\SummarizePark;
use App\Models\Park;
use App\Models\ShareToken;
use Inertia\Inertia;
use Inertia\Response;

class SharedListController extends Controller
{
    /**
     * Public, read-only view of a user's parks list via their share token.
     *
     * 404s unless the token is active and the owner still has sharing on —
     * never reveals that a token exists.
     */
    public function __invoke(string $token, SummarizePark $summarizePark): Response
    {
        $shareToken = ShareToken::query()
            ->active()
            ->with('user')
            ->where('token', $token)
            ->first();

        abort_if($shareToken === null, 404);
        abort_unless($shareToken->user->share_enabled, 404);

        $parks = Park::query()
            ->active()
            ->withVisitStatsFor($shareToken->user->id)
            ->withClosureStatus()
            ->orderBy('name')
            ->get()
            ->map($summarizePark);

        return Inertia::render('shared/Show', [
            'displayName' => $shareToken->user->display_name ?: 'A National Parks explorer',
            'visitedCount' => $parks->where('visited', true)->count(),
            'totalCount' => $parks->count(),
            'parks' => $parks->values(),
        ]);
    }
}
