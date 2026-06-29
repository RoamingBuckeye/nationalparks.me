<?php

declare(strict_types=1);

use App\Models\ShareToken;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('generates a share link', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('sharing.store'))
        ->assertRedirect();

    $token = $user->shareToken()->first();

    expect($token)->not->toBeNull()
        ->and($token->isActive())->toBeTrue();
});

it('rotates the share link', function () {
    $user = User::factory()->create();
    $original = ShareToken::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('sharing.store'))
        ->assertRedirect();

    expect($user->shareToken()->first()->token)->not->toBe($original->token);
});

it('revokes the share link', function () {
    $user = User::factory()->create();
    ShareToken::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('sharing.destroy'))
        ->assertRedirect();

    expect($user->shareToken()->first()->revoked_at)->not->toBeNull();
});

it('shows the sharing settings page with the active link', function () {
    $user = User::factory()->create(['share_enabled' => true]);
    ShareToken::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('sharing.edit'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('settings/Sharing')
            ->where('isActive', true)
            ->where('shareEnabled', true)
            ->whereNot('shareUrl', null));
});

it('requires authentication for sharing settings', function () {
    $this->get(route('sharing.edit'))->assertRedirect(route('login'));
});
