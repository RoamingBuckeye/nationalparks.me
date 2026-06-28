<?php

declare(strict_types=1);

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Testing\FakeNpsClient;
use App\Models\Park;
use Tests\Factories\Nps\ParkDataFactory;

/**
 * Locks the headline invariant: `nps:sync` on a fresh DB lands exactly the
 * canonical-63 set — covering each canonical designation, the seki split,
 * the npsa empty-designation special case, and rejection of non-canonical
 * units.
 */
it('a fresh nps:sync lands exactly the canonical set', function () {
    $fixture = FakeNpsClient::make()->withParks([
        // Plain "National Park" designations — 5 representatives.
        ParkDataFactory::yellowstone(),
        ParkDataFactory::yosemite(),
        ParkDataFactory::make(['id' => 'np-grca', 'parkCode' => 'grca', 'name' => 'Grand Canyon', 'fullName' => 'Grand Canyon National Park']),
        ParkDataFactory::make(['id' => 'np-zion', 'parkCode' => 'zion', 'name' => 'Zion', 'fullName' => 'Zion National Park']),
        ParkDataFactory::make(['id' => 'np-acad', 'parkCode' => 'acad', 'name' => 'Acadia', 'fullName' => 'Acadia National Park']),

        // National Park & Preserve.
        ParkDataFactory::make(['id' => 'np-dena', 'parkCode' => 'dena', 'name' => 'Denali', 'fullName' => 'Denali National Park & Preserve', 'designation' => 'National Park & Preserve']),

        // National and State Parks (Redwood).
        ParkDataFactory::make(['id' => 'np-redw', 'parkCode' => 'redw', 'name' => 'Redwood', 'fullName' => 'Redwood National and State Parks', 'designation' => 'National and State Parks']),

        // npsa — empty designation, in the canonical extras allowlist.
        ParkDataFactory::make(['id' => 'np-npsa', 'parkCode' => 'npsa', 'name' => 'American Samoa', 'fullName' => 'National Park of American Samoa', 'designation' => '']),

        // seki — should be split into sequ + kica, with seki itself absent.
        ParkDataFactory::sequoiaKingsCanyon(),

        // Non-canonical noise that should be filtered out.
        ParkDataFactory::devilsTower(), // National Monument
        ParkDataFactory::make(['id' => 'nm-grsm', 'parkCode' => 'glac', 'name' => 'Glen Canyon NRA', 'fullName' => 'Glen Canyon National Recreation Area', 'designation' => 'National Recreation Area']),
        ParkDataFactory::make(['id' => 'nm-bigh', 'parkCode' => 'bigh', 'name' => 'Bighorn Canyon NRA', 'fullName' => 'Bighorn Canyon National Recreation Area', 'designation' => 'National Recreation Area']),
    ]);

    $this->app->instance(NpsClient::class, $fixture);

    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();

    // 5 plain NPs + 1 NP&Preserve + 1 N&SP + 1 npsa + 2 split children = 10.
    expect(Park::count())->toBe(10);

    expect(Park::pluck('park_code')->sort()->values()->all())
        ->toBe(['acad', 'dena', 'grca', 'kica', 'npsa', 'redw', 'sequ', 'yell', 'yose', 'zion']);

    // seki itself is never persisted — only its split children.
    expect(Park::where('park_code', 'seki')->exists())->toBeFalse();

    // sequ + kica trace back to seki via nps_source_code.
    expect(Park::where('park_code', 'sequ')->value('nps_source_code'))->toBe('seki')
        ->and(Park::where('park_code', 'kica')->value('nps_source_code'))->toBe('seki');

    // Non-canonical units don't slip in.
    expect(Park::where('park_code', 'deto')->exists())->toBeFalse()
        ->and(Park::where('park_code', 'glac')->exists())->toBeFalse()
        ->and(Park::where('park_code', 'bigh')->exists())->toBeFalse();
});

it('a second run is idempotent — still exactly the canonical set', function () {
    $fixture = FakeNpsClient::make()->withParks([
        ParkDataFactory::yellowstone(),
        ParkDataFactory::yosemite(),
        ParkDataFactory::sequoiaKingsCanyon(),
        ParkDataFactory::devilsTower(),
    ]);

    $this->app->instance(NpsClient::class, $fixture);

    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();
    $this->artisan('nps:sync', ['entity' => 'parks'])->assertSuccessful();

    // 2 NPs + 2 split children = 4 (devils tower filtered out both times).
    expect(Park::count())->toBe(4)
        ->and(Park::pluck('park_code')->sort()->values()->all())
        ->toBe(['kica', 'sequ', 'yell', 'yose']);
});
