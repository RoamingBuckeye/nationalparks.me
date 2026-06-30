<?php

declare(strict_types=1);

namespace App\Actions\Api\Auth;

use App\Models\User;

/**
 * A resolved mid-2FA login: the user awaiting a second factor and the device
 * name the eventual token should be issued for.
 */
class PendingChallenge
{
    public function __construct(
        public readonly User $user,
        public readonly string $deviceName,
    ) {}
}
