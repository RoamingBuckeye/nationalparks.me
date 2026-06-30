<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\IssueApiToken;
use App\Actions\Api\Auth\PendingTwoFactorToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        protected readonly PendingTwoFactorToken $pendingToken,
        protected readonly IssueApiToken $issueToken,
    ) {}

    /**
     * Exchange credentials for a Sanctum token. If the account has two-factor
     * enabled, no token is issued — a challenge token is returned instead and
     * the caller must complete the second factor.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if ($user === null || ! Hash::check((string) $request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('The provided credentials are incorrect.')],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            abort(403, __('Your email address is not verified.'));
        }

        $deviceName = (string) $request->string('device_name');

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'two_factor' => true,
                'challenge_token' => $this->pendingToken->issue($user, $deviceName),
            ]);
        }

        return ($this->issueToken)($user, $deviceName);
    }
}
