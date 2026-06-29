<?php

declare(strict_types=1);

use App\Models\Park;
use App\Models\ShareToken;
use App\Models\User;
use App\Models\Visit;
use Inertia\Testing\AssertableInertia;

it('renders an active shared list with the visited state', function () {
    $user = User::factory()->create(['share_enabled' => true, 'display_name' => 'Trailblazer']);
    $token = ShareToken::factory()->for($user)->create();
    $visited = Park::factory()->create();
    Park::factory()->create();
    Visit::factory()->for($user)->for($visited)->create();

    $this->get(route('shared.show', $token->token))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/Show')
            ->where('displayName', 'Trailblazer')
            ->where('visitedCount', 1)
            ->where('totalCount', 2)
            ->has('parks', 2));
});

it('404s when the owner has sharing disabled', function () {
    $user = User::factory()->create(['share_enabled' => false]);
    $token = ShareToken::factory()->for($user)->create();

    $this->get(route('shared.show', $token->token))->assertNotFound();
});

it('404s for a revoked token', function () {
    $user = User::factory()->create(['share_enabled' => true]);
    $token = ShareToken::factory()->revoked()->for($user)->create();

    $this->get(route('shared.show', $token->token))->assertNotFound();
});

it('404s for an unknown token', function () {
    $this->get(route('shared.show', 'does-not-exist'))->assertNotFound();
});

it('does not expose the real name when no display name is set', function () {
    $user = User::factory()->create([
        'share_enabled' => true,
        'display_name' => null,
        'name' => 'Jane Realname',
    ]);
    $token = ShareToken::factory()->for($user)->create();

    $this->get(route('shared.show', $token->token))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('displayName', 'A National Parks explorer'));
});
