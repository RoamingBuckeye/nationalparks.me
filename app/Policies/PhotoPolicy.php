<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    public function view(User $user, Photo $photo): bool
    {
        return $user->id === $photo->uploaded_by_user_id;
    }

    public function delete(User $user, Photo $photo): bool
    {
        return $user->id === $photo->uploaded_by_user_id;
    }
}
