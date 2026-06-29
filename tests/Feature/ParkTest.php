<?php

declare(strict_types=1);

use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Park;
use App\Models\PointOfInterest;
use App\Models\User;
use App\Models\Visit;
use Inertia\Testing\AssertableInertia;

it('lists parks with the user\'s visited state', function () {
    $user = User::factory()->create();
    $visited = Park::factory()->create(['name' => 'Yellowstone']);
    Park::factory()->create(['name' => 'Zion']);
    Visit::factory()->for($user)->for($visited)->create();

    $this->actingAs($user)
        ->get(route('parks.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('parks/Index')
            ->has('parks', 2)
            ->has('states'));
});

it('filters the park list to visited parks', function () {
    $user = User::factory()->create();
    $visited = Park::factory()->create();
    Park::factory()->create();
    Visit::factory()->for($user)->for($visited)->create();

    $this->actingAs($user)
        ->get(route('parks.index', ['visited' => 'visited']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('parks', 1)
            ->where('parks.0.id', $visited->id));
});

it('shows a park with POI counts and the user\'s visits', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();
    PointOfInterest::factory()->count(3)->for($park)->create(['kind' => PoiKind::Place]);
    Visit::factory()->for($user)->for($park)->create();

    $this->actingAs($user)
        ->get(route('parks.show', $park))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('parks/Show')
            ->where('park.id', $park->id)
            ->has('poiCounts', 4)
            ->has('visits', 1));
});

it('requires verification to browse parks', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('parks.index'))
        ->assertRedirect(route('verification.notice'));
});
