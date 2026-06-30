<?php

declare(strict_types=1);

namespace App\Actions\Api\Auth;

use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;
use Throwable;

/**
 * Verify a second factor for the mobile token flow. Accepts any of the three
 * factors the web challenge supports: a TOTP code, the hand-rolled email code,
 * or a recovery code (which is consumed on use).
 */
class VerifyTwoFactorChallenge
{
    public function __construct(
        protected readonly TwoFactorAuthenticationProvider $provider,
    ) {}

    public function __invoke(User $user, ?string $code, ?string $recoveryCode): bool
    {
        if ($recoveryCode !== null && $recoveryCode !== '') {
            return $this->consumeRecoveryCode($user, $recoveryCode);
        }

        if ($code !== null && $code !== '') {
            return $this->verifyTotp($user, $code) || $this->verifyEmailCode($user, $code);
        }

        return false;
    }

    protected function consumeRecoveryCode(User $user, string $recoveryCode): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }

        $match = collect($user->recoveryCodes())
            ->first(fn (string $code): bool => hash_equals($code, $recoveryCode));

        if ($match === null) {
            return false;
        }

        $user->replaceRecoveryCode($match);

        return true;
    }

    protected function verifyTotp(User $user, string $code): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }

        try {
            // A malformed secret (or a non-numeric code) makes the underlying
            // TOTP engine throw; for a verification predicate that is simply a
            // non-match, and lets the email-code fallback run.
            return $this->provider->verify(
                Fortify::currentEncrypter()->decrypt($user->two_factor_secret),
                $code,
            );
        } catch (Throwable) {
            return false;
        }
    }

    protected function verifyEmailCode(User $user, string $code): bool
    {
        $hashedCode = Cache::get(SendTwoFactorEmailCode::cacheKey($user->getKey()));

        return is_string($hashedCode) && Hash::check($code, $hashedCode);
    }
}
