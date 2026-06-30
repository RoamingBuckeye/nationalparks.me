<?php

declare(strict_types=1);

use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Log in and return the challenge token for a 2FA-enabled user.
 */
function startChallenge(User $user): string
{
    return test()->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ])->json('challenge_token');
}

it('issues a token for valid credentials', function () {
    $user = User::factory()->create();

    $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ])
        ->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email', 'two_factor_enabled']])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.id', $user->id);

    expect($user->tokens()->count())->toBe(1);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'iPhone',
    ])->assertStatus(422)->assertJsonValidationErrors('email');

    expect($user->tokens()->count())->toBe(0);
});

it('blocks login until the email is verified', function () {
    $user = User::factory()->unverified()->create();

    $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ])->assertForbidden();

    expect($user->tokens()->count())->toBe(0);
});

it('returns a 2FA challenge instead of a token when two-factor is enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ])
        ->assertOk()
        ->assertJsonPath('two_factor', true)
        ->assertJsonStructure(['challenge_token'])
        ->assertJsonMissingPath('token');

    expect($user->tokens()->count())->toBe(0);
});

it('completes the 2FA challenge with an emailed code and issues a token', function () {
    $user = User::factory()->withTwoFactor()->create();
    $challengeToken = startChallenge($user);

    Cache::put(SendTwoFactorEmailCode::cacheKey($user->id), Hash::make('123456'), now()->addMinutes(10));

    $this->postJson(route('api.two-factor.challenge'), [
        'challenge_token' => $challengeToken,
        'code' => '123456',
    ])
        ->assertOk()
        ->assertJsonStructure(['token', 'user'])
        ->assertJsonPath('user.id', $user->id);

    expect($user->tokens()->count())->toBe(1);
    // The emailed code is single-use.
    expect(Cache::has(SendTwoFactorEmailCode::cacheKey($user->id)))->toBeFalse();
});

it('completes the 2FA challenge with a recovery code and consumes it', function () {
    $user = User::factory()->withTwoFactor()->create();
    $challengeToken = startChallenge($user);

    $this->postJson(route('api.two-factor.challenge'), [
        'challenge_token' => $challengeToken,
        'recovery_code' => 'recovery-code-1',
    ])->assertOk()->assertJsonStructure(['token']);

    expect($user->tokens()->count())->toBe(1);
    expect($user->fresh()->recoveryCodes())->not->toContain('recovery-code-1');
});

it('rejects an invalid 2FA code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $challengeToken = startChallenge($user);

    $this->postJson(route('api.two-factor.challenge'), [
        'challenge_token' => $challengeToken,
        'code' => '000000',
    ])->assertStatus(422)->assertJsonValidationErrors('code');

    expect($user->tokens()->count())->toBe(0);
});

it('rejects an unknown or expired challenge token', function () {
    $this->postJson(route('api.two-factor.challenge'), [
        'challenge_token' => 'does-not-exist',
        'code' => '123456',
    ])->assertStatus(422)->assertJsonValidationErrors('challenge_token');
});

it('requires a code or recovery code to complete the challenge', function () {
    $user = User::factory()->withTwoFactor()->create();
    $challengeToken = startChallenge($user);

    $this->postJson(route('api.two-factor.challenge'), [
        'challenge_token' => $challengeToken,
    ])->assertStatus(422)->assertJsonValidationErrors('code');
});

it('emails a 2FA code for a pending challenge', function () {
    Mail::fake();

    $user = User::factory()->withTwoFactor()->create();
    $challengeToken = startChallenge($user);

    $this->postJson(route('api.two-factor.email-code'), [
        'challenge_token' => $challengeToken,
    ])->assertOk();

    Mail::assertQueued(
        TwoFactorCodeMail::class,
        fn (TwoFactorCodeMail $mail) => $mail->hasTo($user->email),
    );
    expect(Cache::has(SendTwoFactorEmailCode::cacheKey($user->id)))->toBeTrue();
});

it('returns the authenticated user for a valid token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('iPhone')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('api.user'))
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});

it('rejects API access without a token', function () {
    $this->getJson(route('api.user'))->assertUnauthorized();
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('iPhone')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.logout'))
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});
