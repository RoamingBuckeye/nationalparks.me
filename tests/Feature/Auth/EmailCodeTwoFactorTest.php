<?php

declare(strict_types=1);

use App\Actions\Auth\SendTwoFactorEmailCode;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

it('emails a one-time code to the user pending a 2FA challenge', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->withSession(['login.id' => $user->id])
        ->post(route('two-factor.email-code'))
        ->assertRedirect();

    Mail::assertQueued(
        TwoFactorCodeMail::class,
        fn (TwoFactorCodeMail $mail) => $mail->hasTo($user->email),
    );

    expect(Cache::has(SendTwoFactorEmailCode::cacheKey($user->id)))->toBeTrue();
});

it('refuses to send a code without a pending login', function () {
    Mail::fake();

    $this->post(route('two-factor.email-code'))->assertStatus(419);

    Mail::assertNothingQueued();
});

it('completes login with a valid emailed code', function () {
    $user = User::factory()->create();

    Cache::put(SendTwoFactorEmailCode::cacheKey($user->id), Hash::make('123456'), now()->addMinutes(10));

    $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post(route('two-factor.email-challenge'), ['code' => '123456'])
        ->assertRedirect();

    $this->assertAuthenticatedAs($user);

    // The code is single-use.
    expect(Cache::has(SendTwoFactorEmailCode::cacheKey($user->id)))->toBeFalse();
});

it('rejects an invalid or expired emailed code', function () {
    $user = User::factory()->create();

    Cache::put(SendTwoFactorEmailCode::cacheKey($user->id), Hash::make('123456'), now()->addMinutes(10));

    $this->withSession(['login.id' => $user->id])
        ->post(route('two-factor.email-challenge'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

it('forbids the email challenge without a pending login', function () {
    $this->post(route('two-factor.email-challenge'), ['code' => '123456'])
        ->assertForbidden();

    $this->assertGuest();
});
