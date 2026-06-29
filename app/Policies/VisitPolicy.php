<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function view(User $user, Visit $visit): bool
    {
        return $user->id === $visit->user_id;
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->id === $visit->user_id;
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->id === $visit->user_id;
    }
}
