<?php

declare(strict_types=1);

use App\Actions\Stamps\EvaluateStamps;
use App\Actions\Stamps\SummarizeStampsForUser;
use App\Models\Park;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\StampSeeder;
use Inertia\Testing\AssertableInertia;

it('renders the stamps page with the full catalog and counts', function () {
    $this->seed(StampSeeder::class);
    $user = User::factory()->create();

    $wv = Park::factory()->create(['states' => ['WV']]);
    Visit::factory()->for($user)->for($wv)->create();
    app(EvaluateStamps::class)($user);

    $this->actingAs($user)
        ->get(route('stamps'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('stamps/Index')
            ->where('totalCount', 45)
            ->where('earnedCount', fn (int $count): bool => $count >= 2) // First Stamp + Mountaineer
            ->has('stamps', 45)
            ->where('stamps', fn ($stamps): bool => collect($stamps)->firstWhere('slug', 'state-wv')['earned'] === true));
});

it('requires email verification to view stamps', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('stamps'))
        ->assertRedirect(route('verification.notice'));
});

it('computes earned state and progress for each stamp', function () {
    $this->seed(StampSeeder::class);
    $user = User::factory()->create();

    $caParks = Park::factory()->count(3)->create(['states' => ['CA']]);
    Visit::factory()->for($user)->for($caParks[0])->create();
    Visit::factory()->for($user)->for($caParks[1])->create();

    $golden = app(SummarizeStampsForUser::class)($user)->firstWhere('slug', 'state-ca');

    expect($golden['name'])->toBe('Golden State')
        ->and($golden['earned'])->toBeFalse()
        ->and($golden['progress'])->toBe(2)
        ->and($golden['required'])->toBe(3);

    Visit::factory()->for($user)->for($caParks[2])->create();
    app(EvaluateStamps::class)($user);

    $earned = app(SummarizeStampsForUser::class)($user)->firstWhere('slug', 'state-ca');
    expect($earned['earned'])->toBeTrue();
});
