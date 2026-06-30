<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * The authenticated user behind the current access token.
     */
    public function __invoke(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
