<?php

declare(strict_types=1);

use App\Models\Park;
use App\Models\PointOfInterest;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPointOfInterest;
use Illuminate\Database\UniqueConstraintViolationException;

it('belongs to a user and a park', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();
    $visit = Visit::factory()->for($user)->for($park)->create();

    expect($visit->user->is($user))->toBeTrue()
        ->and($visit->park->is($park))->toBeTrue();
});

it('treats ended_at=null as live', function () {
    $live = Visit::factory()->live()->create();
    $logged = Visit::factory()->create();

    expect($live->isLive())->toBeTrue()
        ->and($live->ended_at)->toBeNull()
        ->and($logged->isLive())->toBeFalse()
        ->and($logged->ended_at)->not->toBeNull();
});

it('checks off a POI exactly once per (visit, poi)', function () {
    $visit = Visit::factory()->create();
    $poi = PointOfInterest::factory()->recycle($visit->park)->create();

    VisitPointOfInterest::create([
        'visit_id' => $visit->id,
        'point_of_interest_id' => $poi->id,
        'checked_at' => now(),
    ]);

    expect(fn () => VisitPointOfInterest::create([
        'visit_id' => $visit->id,
        'point_of_interest_id' => $poi->id,
        'checked_at' => now(),
    ]))->toThrow(UniqueConstraintViolationException::class);
});

it('cascades visit_pois on visit delete', function () {
    $visit = Visit::factory()->create();
    VisitPointOfInterest::factory()->for($visit)->count(3)->create();

    $visit->delete();

    expect(VisitPointOfInterest::count())->toBe(0);
});
