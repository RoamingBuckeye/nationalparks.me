<?php

declare(strict_types=1);

use App\Models\Park;
use App\Models\PointOfInterest;
use App\Models\User;
use App\Models\Visit;

it('checks a POI off then unchecks it', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();
    $poi = PointOfInterest::factory()->for($park)->create();
    $visit = Visit::factory()->for($user)->for($park)->create();

    $route = route('visits.pois.toggle', ['visit' => $visit, 'pointOfInterest' => $poi]);

    $this->actingAs($user)->post($route)->assertRedirect();
    expect($visit->visitPois()->count())->toBe(1);

    $this->actingAs($user)->post($route)->assertRedirect();
    expect($visit->visitPois()->count())->toBe(0);
});

it('rejects a POI that belongs to a different park', function () {
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->for(Park::factory())->create();
    $foreignPoi = PointOfInterest::factory()->for(Park::factory())->create();

    $this->actingAs($user)
        ->post(route('visits.pois.toggle', ['visit' => $visit, 'pointOfInterest' => $foreignPoi]))
        ->assertStatus(422);

    expect($visit->visitPois()->count())->toBe(0);
});

it('forbids toggling a POI on another user\'s visit', function () {
    $park = Park::factory()->create();
    $poi = PointOfInterest::factory()->for($park)->create();
    $visit = Visit::factory()->for(User::factory())->for($park)->create();

    $this->actingAs(User::factory()->create())
        ->post(route('visits.pois.toggle', ['visit' => $visit, 'pointOfInterest' => $poi]))
        ->assertForbidden();
});
