<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\IssueApiToken;
use App\Actions\Api\Auth\PendingTwoFactorToken;
use App\Actions\Api\Auth\VerifyTwoFactorChallenge;
use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TwoFactorChallengeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        protected readonly PendingTwoFactorToken $pendingToken,
        protected readonly VerifyTwoFactorChallenge $verifyChallenge,
        protected readonly IssueApiToken $issueToken,
    ) {}

    /**
     * Complete a pending two-factor login (TOTP, email code, or recovery code)
     * and issue the Sanctum token.
     */
    public function __invoke(TwoFactorChallengeRequest $request): JsonResponse
    {
        $challengeToken = (string) $request->string('challenge_token');
        $pending = $this->pendingToken->resolve($challengeToken);

        if ($pending === null) {
            throw ValidationException::withMessages([
                'challenge_token' => [__('This login challenge has expired. Please sign in again.')],
            ]);
        }

        $passed = ($this->verifyChallenge)(
            $pending->user,
            $request->filled('code') ? (string) $request->string('code') : null,
            $request->filled('recovery_code') ? (string) $request->string('recovery_code') : null,
        );

        if (! $passed) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        $this->pendingToken->forget($challengeToken);
        Cache::forget(SendTwoFactorEmailCode::cacheKey($pending->user->getKey()));

        return ($this->issueToken)($pending->user, $pending->deviceName);
    }
}
