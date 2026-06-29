<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TwoFactorEmailCodeController extends Controller
{
    /**
     * Email a one-time code to the user currently pending a 2FA challenge.
     */
    public function __invoke(Request $request, SendTwoFactorEmailCode $sendCode): RedirectResponse
    {
        $userId = $request->session()->get('login.id');

        abort_if($userId === null, 419);

        $sendCode(User::query()->whereKey($userId)->firstOrFail());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('We emailed you a login code.'),
        ]);

        return back();
    }
}
