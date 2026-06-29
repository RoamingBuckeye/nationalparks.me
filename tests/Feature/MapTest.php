<?php

declare(strict_types=1);

use App\Models\Park;
use App\Models\User;
use App\Models\Visit;
use Inertia\Testing\AssertableInertia;

it('renders the map with parks and the user\'s visited state', function () {
    $user = User::factory()->create();
    $visited = Park::factory()->create(['latitude' => 44.6, 'longitude' => -110.5]);
    Park::factory()->create(['latitude' => 37.8, 'longitude' => -119.5]);
    Park::factory()->create(['latitude' => null, 'longitude' => null]);
    Visit::factory()->for($user)->for($visited)->create();

    $this->actingAs($user)
        ->get(route('map'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Map')
            ->has('parks', 2) // the coordinate-less park is excluded
            ->where('visitedCount', 1)
            ->where('totalCount', 2)
            ->where('parks.0.visited', fn (bool $visited) => is_bool($visited)));
});

it('requires verification for the map', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('map'))
        ->assertRedirect(route('verification.notice'));
});
