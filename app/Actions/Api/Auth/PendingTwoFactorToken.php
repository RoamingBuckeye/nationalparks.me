<?php

declare(strict_types=1);

namespace App\Actions\Api\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Stateless replacement for Fortify's session `login.id`: a short-lived,
 * cache-backed handle that lets the mobile API resume a login after the
 * second factor without holding a session.
 */
class PendingTwoFactorToken
{
    /**
     * Issue a challenge token for a user whose primary credentials passed but
     * who still owes a second factor. Expires in 10 minutes.
     */
    public function issue(User $user, string $deviceName): string
    {
        $token = Str::random(40);

        Cache::put(
            self::cacheKey($token),
            ['user_id' => $user->getKey(), 'device_name' => $deviceName],
            now()->addMinutes(10),
        );

        return $token;
    }

    /**
     * Resolve a challenge token back to the pending user + device name, or null
     * if the token is unknown, expired, or its user no longer exists.
     */
    public function resolve(string $token): ?PendingChallenge
    {
        $data = Cache::get(self::cacheKey($token));

        if (! is_array($data) || ! isset($data['user_id'], $data['device_name'])) {
            return null;
        }

        $user = User::find((int) $data['user_id']);

        if ($user === null) {
            return null;
        }

        return new PendingChallenge($user, (string) $data['device_name']);
    }

    public function forget(string $token): void
    {
        Cache::forget(self::cacheKey($token));
    }

    protected static function cacheKey(string $token): string
    {
        return 'api-2fa-challenge:'.hash('sha256', $token);
    }
}
