<?php

declare(strict_types=1);

use App\Actions\Stamps\EvaluateStamps;
use App\Enums\PassportRegion;
use App\Models\Park;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserStamp;
use App\Models\Visit;
use Illuminate\Support\Collection;

/**
 * @return Collection<int, Stamp>
 */
function evaluateFor(User $user)
{
    return app(EvaluateStamps::class)($user);
}

it('awards a park-count milestone only once the threshold is met', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->parkCount(3)->create();

    Park::factory()->count(2)->create()->each(
        fn (Park $park) => Visit::factory()->for($user)->for($park)->create(),
    );

    expect(evaluateFor($user))->toHaveCount(0);
    expect($user->userStamps()->count())->toBe(0);

    Visit::factory()->for($user)->for(Park::factory()->create())->create();

    expect(evaluateFor($user)->pluck('id'))->toContain($stamp->id);
    expect($user->userStamps()->where('stamp_id', $stamp->id)->exists())->toBeTrue();
});

it('counts distinct parks, not repeat visits', function () {
    $user = User::factory()->create();
    Stamp::factory()->parkCount(2)->create();
    $park = Park::factory()->create();

    Visit::factory()->count(3)->for($user)->for($park)->create();

    expect(evaluateFor($user))->toHaveCount(0);
});

it('awards a state collection when all of the state\'s parks are visited', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->stateSet('UT')->create();

    $utahParks = Park::factory()->count(3)->create(['states' => ['UT']]);
    Park::factory()->create(['states' => ['CO']]); // decoy, another state

    Visit::factory()->for($user)->for($utahParks[0])->create();
    Visit::factory()->for($user)->for($utahParks[1])->create();
    expect(evaluateFor($user))->toHaveCount(0);

    Visit::factory()->for($user)->for($utahParks[2])->create();
    expect(evaluateFor($user)->pluck('id'))->toContain($stamp->id);
});

it('awards a state collection with a partial required_count', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->stateSet('CA', 2)->create();

    $caParks = Park::factory()->count(4)->create(['states' => ['CA']]);

    Visit::factory()->for($user)->for($caParks[0])->create();
    expect(evaluateFor($user))->toHaveCount(0);

    Visit::factory()->for($user)->for($caParks[1])->create();
    expect(evaluateFor($user)->pluck('id'))->toContain($stamp->id);
});

it('awards a region collection across its states', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->regionSet(PassportRegion::RockyMountain)->create();

    // Rocky Mountain includes UT, CO, MT, WY, ID. Only two parks exist here,
    // so "all members" == 2.
    $utah = Park::factory()->create(['states' => ['UT']]);
    $colorado = Park::factory()->create(['states' => ['CO']]);

    Visit::factory()->for($user)->for($utah)->create();
    expect(evaluateFor($user))->toHaveCount(0);

    Visit::factory()->for($user)->for($colorado)->create();
    expect(evaluateFor($user)->pluck('id'))->toContain($stamp->id);
});

it('earns both state collections for a park that spans two states', function () {
    $user = User::factory()->create();
    $tennessee = Stamp::factory()->stateSet('TN')->create();
    $northCarolina = Stamp::factory()->stateSet('NC')->create();

    $smokies = Park::factory()->create(['states' => ['TN', 'NC']]);
    Visit::factory()->for($user)->for($smokies)->create();

    $awarded = evaluateFor($user)->pluck('id');
    expect($awarded)->toContain($tennessee->id)->toContain($northCarolina->id);
});

it('keeps earned stamps sticky when a visit is later deleted', function () {
    $user = User::factory()->create();
    Stamp::factory()->parkCount(1)->create();
    $visit = Visit::factory()->for($user)->for(Park::factory()->create())->create();

    evaluateFor($user);
    expect($user->userStamps()->count())->toBe(1);

    $visit->delete();
    evaluateFor($user);

    expect($user->userStamps()->count())->toBe(1);
});

it('is idempotent across repeated evaluation', function () {
    $user = User::factory()->create();
    Stamp::factory()->parkCount(1)->create();
    Visit::factory()->for($user)->for(Park::factory()->create())->create();

    evaluateFor($user);
    evaluateFor($user);

    expect(UserStamp::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('ignores inactive stamps', function () {
    $user = User::factory()->create();
    Stamp::factory()->parkCount(1)->inactive()->create();
    Visit::factory()->for($user)->for(Park::factory()->create())->create();

    expect(evaluateFor($user))->toHaveCount(0);
});

it('awards stamps on check-in', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->parkCount(1)->create();
    $park = Park::factory()->create();

    $this->actingAs($user)
        ->post(route('visits.store'), ['park_id' => $park->id])
        ->assertRedirect();

    expect($user->userStamps()->where('stamp_id', $stamp->id)->exists())->toBeTrue();
});

it('backfills qualifying users via the stamps:evaluate command', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->parkCount(1)->create();
    Visit::factory()->for($user)->for(Park::factory()->create())->create();

    expect($user->userStamps()->count())->toBe(0);

    $this->artisan('stamps:evaluate')->assertSuccessful();

    expect($user->userStamps()->where('stamp_id', $stamp->id)->exists())->toBeTrue();
});

it('flags an award as vintage when earned before the stamp changed', function () {
    $stamp = Stamp::factory()->stateSet('UT')->create(['members_changed_at' => now()]);

    $old = UserStamp::factory()->create(['stamp_id' => $stamp->id, 'earned_at' => now()->subYear()]);
    $new = UserStamp::factory()->create(['stamp_id' => $stamp->id, 'earned_at' => now()->addDay()]);

    expect($old->isVintage())->toBeTrue();
    expect($new->isVintage())->toBeFalse();
});
