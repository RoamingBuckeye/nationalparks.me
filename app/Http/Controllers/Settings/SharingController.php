<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ShareToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SharingController extends Controller
{
    /**
     * Show the sharing settings: the public link and its state.
     */
    public function edit(Request $request): Response
    {
        $token = $request->user()->shareToken;
        $isActive = $token !== null && $token->isActive();

        return Inertia::render('settings/Sharing', [
            'shareUrl' => $isActive ? route('shared.show', $token->token) : null,
            'isActive' => $isActive,
            'shareEnabled' => $request->user()->share_enabled,
        ]);
    }

    /**
     * Generate a fresh share link (or rotate the existing one).
     */
    public function store(Request $request): RedirectResponse
    {
        ShareToken::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['token' => ShareToken::generate(), 'revoked_at' => null],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Share link ready.')]);

        return back();
    }

    /**
     * Revoke the current share link so its URL stops working.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->shareToken()->update(['revoked_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Share link revoked.')]);

        return back();
    }
}
