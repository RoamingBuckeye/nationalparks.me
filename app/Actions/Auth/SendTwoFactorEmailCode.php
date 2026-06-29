<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SendTwoFactorEmailCode
{
    /**
     * Generate a one-time login code, stash a hash of it, and email the user.
     */
    public function __invoke(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(self::cacheKey($user->getKey()), Hash::make($code), now()->addMinutes(10));

        Mail::to($user)->send(new TwoFactorCodeMail($code));
    }

    /**
     * Cache key holding the hashed, unexpired code for a pending login.
     */
    public static function cacheKey(int|string $userId): string
    {
        return 'two-factor-email-code:'.$userId;
    }
}
