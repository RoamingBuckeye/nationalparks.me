<?php

declare(strict_types=1);

use App\Models\ShareToken;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

it('defaults share_enabled to false and casts as bool', function () {
    $user = User::factory()->create()->refresh();

    expect($user->share_enabled)->toBeFalse()
        ->and($user->shareToken)->toBeNull();
});

it('persists a generated share token via the factory and exposes it via the relation', function () {
    $user = User::factory()->create();
    ShareToken::factory()->for($user)->create();

    expect($user->refresh()->shareToken)->toBeInstanceOf(ShareToken::class)
        ->and($user->shareToken->isActive())->toBeTrue()
        ->and(strlen($user->shareToken->token))->toBe(40);
});

it('enforces one share token per user', function () {
    $user = User::factory()->create();
    ShareToken::factory()->for($user)->create();

    expect(fn () => ShareToken::factory()->for($user)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});

it('only returns active tokens via the scope', function () {
    ShareToken::factory()->count(2)->create();
    ShareToken::factory()->revoked()->create();

    expect(ShareToken::query()->active()->count())->toBe(2);
});
