<?php

declare(strict_types=1);

namespace App\Actions\Api\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Mint a Sanctum personal access token for a device and shape the standard
 * login success payload (token + the authenticated user). Tokens do not
 * expire — the app holds them until logout.
 */
class IssueApiToken
{
    public function __invoke(User $user, string $deviceName): JsonResponse
    {
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }
}
