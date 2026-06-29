<?php

declare(strict_types=1);

use App\Models\Park;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPointOfInterest;

it('logs a live check-in', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();

    $this->actingAs($user)
        ->post(route('visits.store'), ['park_id' => $park->id])
        ->assertRedirect();

    $visit = Visit::sole();

    expect($visit->user_id)->toBe($user->id)
        ->and($visit->park_id)->toBe($park->id)
        ->and($visit->isLive())->toBeTrue();
});

it('logs a backdated visit with a journal', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();

    $this->actingAs($user)
        ->post(route('visits.store'), [
            'park_id' => $park->id,
            'started_at' => '2025-07-01',
            'ended_at' => '2025-07-03',
            'notes' => 'Saw a bison up close.',
        ])
        ->assertRedirect();

    $visit = Visit::sole();

    expect($visit->isLive())->toBeFalse()
        ->and($visit->notes)->toBe('Saw a bison up close.');
});

it('rejects a visit that starts in the future', function () {
    $user = User::factory()->create();
    $park = Park::factory()->create();

    $this->actingAs($user)
        ->post(route('visits.store'), [
            'park_id' => $park->id,
            'started_at' => now()->addDay()->toDateString(),
        ])
        ->assertSessionHasErrors('started_at');

    expect(Visit::count())->toBe(0);
});

it('updates a visit\'s dates and journal', function () {
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('visits.update', $visit), [
            'started_at' => '2025-01-01',
            'notes' => 'Updated journal.',
        ])
        ->assertRedirect();

    expect($visit->refresh()->notes)->toBe('Updated journal.');
});

it('forbids updating another user\'s visit', function () {
    $visit = Visit::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('visits.update', $visit), ['started_at' => '2025-01-01'])
        ->assertForbidden();
});

it('forbids viewing another user\'s visit', function () {
    $visit = Visit::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->get(route('visits.show', $visit))
        ->assertForbidden();
});

it('deletes a visit and its checked POIs', function () {
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();
    VisitPointOfInterest::factory()->for($visit)->create();

    $this->actingAs($user)
        ->delete(route('visits.destroy', $visit))
        ->assertRedirect();

    $this->assertModelMissing($visit);
    expect(VisitPointOfInterest::count())->toBe(0);
});
