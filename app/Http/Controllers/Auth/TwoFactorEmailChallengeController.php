<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorEmailChallengeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\Fortify;

class TwoFactorEmailChallengeController extends Controller
{
    /**
     * Complete a pending login using a valid emailed code.
     *
     * Mirrors how Fortify finalizes the two-factor challenge: it reads the
     * pending user from the session, logs them in, and clears the login state.
     */
    public function __invoke(TwoFactorEmailChallengeRequest $request): RedirectResponse
    {
        $user = User::query()->whereKey($request->session()->get('login.id'))->firstOrFail();

        Cache::forget(SendTwoFactorEmailCode::cacheKey($user->getKey()));

        Auth::guard()->login($user, (bool) $request->session()->get('login.remember', false));

        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();

        return redirect()->intended(Fortify::redirects('login'));
    }
}
