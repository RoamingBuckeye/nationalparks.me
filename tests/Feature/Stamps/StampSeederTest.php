<?php

declare(strict_types=1);

use App\Enums\PassportRegion;
use App\Enums\StampCriteria;
use App\Models\Park;
use App\Models\Stamp;
use App\Models\User;
use Database\Seeders\StampSeeder;

it('seeds the full stamp catalog', function () {
    $this->seed(StampSeeder::class);

    // 5 milestones + 32 state/territory collections + 8 regions.
    expect(Stamp::count())->toBe(45)
        ->and(Stamp::where('criteria_type', StampCriteria::ParkCount)->count())->toBe(5)
        ->and(Stamp::where('criteria_type', StampCriteria::StateSet)->count())->toBe(32)
        ->and(Stamp::where('criteria_type', StampCriteria::RegionSet)->count())->toBe(8);
});

it('seeds Utah as the Mighty Five state collection in its region color', function () {
    $this->seed(StampSeeder::class);

    $utah = Stamp::where('slug', 'state-ut')->sole();

    expect($utah->name)->toBe('Mighty Five')
        ->and($utah->criteria_type)->toBe(StampCriteria::StateSet)
        ->and($utah->state_code)->toBe('UT')
        ->and($utah->accent_color)->toBe(PassportRegion::RockyMountain->color());
});

it('seeds region stamps in their official Passport colors', function () {
    $this->seed(StampSeeder::class);

    $region = Stamp::where('slug', 'region-southeast')->sole();

    expect($region->criteria_type)->toBe(StampCriteria::RegionSet)
        ->and($region->region)->toBe(PassportRegion::Southeast)
        ->and($region->accent_color)->toBe(PassportRegion::Southeast->color());
});

it('is idempotent when re-run', function () {
    $this->seed(StampSeeder::class);
    $this->seed(StampSeeder::class);

    expect(Stamp::count())->toBe(45);
});

it('awards seeded stamps end to end on check-in', function () {
    $this->seed(StampSeeder::class);

    $user = User::factory()->create();
    $newRiverGorge = Park::factory()->create(['states' => ['WV']]);

    $this->actingAs($user)
        ->post(route('visits.store'), ['park_id' => $newRiverGorge->id])
        ->assertRedirect();

    $earned = $user->stamps()->pluck('slug');

    expect($earned)->toContain('first-stamp')   // any 1 park
        ->toContain('state-wv');                // Mountaineer — WV's only park
});
