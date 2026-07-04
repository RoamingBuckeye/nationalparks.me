<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Stamps\SummarizeStampsForUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StampsController extends Controller
{
    /**
     * The user's stamp collection — every stamp with earned state and progress.
     */
    public function __invoke(Request $request, SummarizeStampsForUser $summarizeStamps): Response
    {
        $stamps = $summarizeStamps($request->user());

        return Inertia::render('stamps/Index', [
            'stamps' => $stamps,
            'earnedCount' => $stamps->where('earned', true)->count(),
            'totalCount' => $stamps->count(),
        ]);
    }
}
