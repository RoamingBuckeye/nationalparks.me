<?php

declare(strict_types=1);

use App\Integrations\Nps\Enums\AlertCategory;
use App\Integrations\Nps\Enums\PoiKind;
use App\Models\Alert;
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

it('shows active alerts severity-ordered and excludes archived ones', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();
    Alert::factory()->for($park)->category(AlertCategory::Information)->create();
    Alert::factory()->for($park)->category(AlertCategory::Danger)->create();
    Alert::factory()->for($park)->archived()->category(AlertCategory::Caution)->create();

    $this->actingAs($user)
        ->get(route('parks.show', $park))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('alerts', 2)
            ->where('alerts.0.category', 'Danger')
            ->where('alerts.1.category', 'Information'));
});

it('flags parks with an active closure on the list', function () {
    $user = User::factory()->create();
    $closed = Park::factory()->create(['name' => 'Closed Canyon']);
    Park::factory()->create(['name' => 'Open Range']);

    Alert::factory()->for($closed)->category(AlertCategory::ParkClosure)->create();

    $this->actingAs($user)
        ->get(route('parks.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('parks.0.closed', true)
            ->where('parks.1.closed', false));
});

it('ignores archived and non-closure alerts for the closure flag', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();

    Alert::factory()->for($park)->category(AlertCategory::ParkClosure)->archived()->create();
    Alert::factory()->for($park)->category(AlertCategory::Danger)->create();

    $this->actingAs($user)
        ->get(route('parks.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('parks.0.closed', false));
});

it('requires verification to browse parks', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('parks.index'))
        ->assertRedirect(route('verification.notice'));
});
