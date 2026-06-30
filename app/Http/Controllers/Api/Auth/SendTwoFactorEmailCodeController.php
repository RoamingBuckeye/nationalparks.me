<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\PendingTwoFactorToken;
use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendTwoFactorEmailCodeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SendTwoFactorEmailCodeController extends Controller
{
    public function __construct(
        protected readonly PendingTwoFactorToken $pendingToken,
        protected readonly SendTwoFactorEmailCode $sendCode,
    ) {}

    /**
     * Email a one-time code to a user who is mid-2FA, as an alternative to
     * their authenticator app.
     */
    public function __invoke(SendTwoFactorEmailCodeRequest $request): JsonResponse
    {
        $pending = $this->pendingToken->resolve((string) $request->string('challenge_token'));

        if ($pending === null) {
            throw ValidationException::withMessages([
                'challenge_token' => [__('This login challenge has expired. Please sign in again.')],
            ]);
        }

        ($this->sendCode)($pending->user);

        return response()->json([
            'message' => __('A two factor authentication code has been emailed to you.'),
        ]);
    }
}
