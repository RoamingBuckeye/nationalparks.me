<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\UsState;
use App\Enums\PassportRegion;
use App\Enums\StampCriteria;
use App\Models\Stamp;
use Illuminate\Database\Seeder;

/**
 * The stamp catalog: count milestones, one collection per state/territory with
 * a national park, and the eight Passport regions. Membership for state and
 * region stamps is derived from `parks.states` at evaluation time, so nothing
 * here references specific parks. Idempotent — safe to re-run.
 */
class StampSeeder extends Seeder
{
    /**
     * Any N distinct parks. slug => [name, count, accent color, description].
     *
     * @var list<array{string, string, int, string, string}>
     */
    protected const array MILESTONES = [
        ['first-stamp', 'First Stamp', 1, '#6FB7D4', 'Check into your first national park.'],
        ['high-five', 'High Five', 5, '#2F7D46', 'Visit any 5 national parks.'],
        ['trailblazer', 'Trailblazer', 10, '#E08A2E', 'Visit any 10 national parks.'],
        ['explorer', 'Explorer', 25, '#7A5EA6', 'Visit any 25 national parks.'],
        ['sixty-three-club', 'The 63 Club', 63, '#E6B325', 'Visit all 63 national parks.'],
    ];

    /**
     * One collection per park-bearing state/territory, grouped by region so
     * each inherits its region's official Passport color. [state code, name].
     *
     * @var list<array{PassportRegion, list<array{string, string}>}>
     */
    protected const array STATE_COLLECTIONS = [
        [PassportRegion::NorthAtlantic, [['ME', 'Down East']]],
        [PassportRegion::MidAtlantic, [['VA', 'Old Dominion'], ['WV', 'Mountaineer']]],
        [PassportRegion::Southeast, [
            ['KY', 'Bluegrass'], ['TN', 'Volunteer'], ['NC', 'Tar Heel'],
            ['SC', 'Palmetto'], ['FL', 'Sunshine State'], ['VI', 'U.S. Virgin Islands'],
        ]],
        [PassportRegion::Midwest, [
            ['OH', 'Buckeye'], ['IN', 'Hoosier'], ['MI', 'Wolverine'], ['MN', 'North Star'],
            ['MO', 'Gateway'], ['AR', 'The Natural State'], ['ND', 'Roughrider'], ['SD', 'Coyote State'],
        ]],
        [PassportRegion::Southwest, [['TX', 'Lone Star'], ['NM', 'Land of Enchantment'], ['AZ', 'Grand Canyon State']]],
        [PassportRegion::RockyMountain, [
            ['UT', 'Mighty Five'], ['CO', 'Colorful Colorado'], ['MT', 'Big Sky'],
            ['WY', 'Cowboy State'], ['ID', 'Gem State'],
        ]],
        [PassportRegion::West, [['CA', 'Golden State'], ['NV', 'Silver State'], ['HI', 'Aloha State'], ['AS', 'American Samoa']]],
        [PassportRegion::PacificNorthwestAlaska, [['WA', 'Evergreen State'], ['OR', 'Beaver State'], ['AK', 'The Last Frontier']]],
    ];

    public function run(): void
    {
        $this->seedMilestones();
        $this->seedStateCollections();
        $this->seedRegions();
    }

    protected function seedMilestones(): void
    {
        foreach (self::MILESTONES as $order => [$slug, $name, $count, $color, $description]) {
            $this->upsert($slug, [
                'name' => $name,
                'description' => $description,
                'criteria_type' => StampCriteria::ParkCount,
                'required_count' => $count,
                'accent_color' => $color,
                'category' => 'Milestones',
                'sort_order' => $order,
            ]);
        }
    }

    protected function seedStateCollections(): void
    {
        $order = 0;

        foreach (self::STATE_COLLECTIONS as [$region, $states]) {
            foreach ($states as [$code, $name]) {
                $place = UsState::from($code)->fullName();

                $this->upsert('state-'.strtolower($code), [
                    'name' => $name,
                    'description' => "Visit every national park in {$place}.",
                    'criteria_type' => StampCriteria::StateSet,
                    'state_code' => $code,
                    'accent_color' => $region->color(),
                    'category' => 'State Collections',
                    'sort_order' => $order++,
                ]);
            }
        }
    }

    protected function seedRegions(): void
    {
        foreach (PassportRegion::cases() as $order => $region) {
            $this->upsert('region-'.$region->value, [
                'name' => $region->label(),
                'description' => "Visit every national park in the {$region->label()} region.",
                'criteria_type' => StampCriteria::RegionSet,
                'region' => $region,
                'accent_color' => $region->color(),
                'category' => 'Regions',
                'sort_order' => $order,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function upsert(string $slug, array $attributes): void
    {
        // `scene` doubles as the artwork key; default it to the (stable) slug.
        Stamp::updateOrCreate(['slug' => $slug], [...$attributes, 'scene' => $slug]);
    }
}
