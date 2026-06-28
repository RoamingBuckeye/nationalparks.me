<?php

declare(strict_types=1);

namespace Tests\Factories\Nps;

use App\Integrations\Nps\Data\ParkData;

/**
 * Realistic ParkData fixtures for tests. Field shapes match what the live
 * NPS API returns (strings for numerics, CSV for states, etc.) so the
 * fromArray casts exercise the same code paths.
 */
final class ParkDataFactory
{
    /** @param array<string, mixed> $overrides */
    public static function yellowstone(array $overrides = []): ParkData
    {
        return ParkData::fromArray(array_replace([
            'id' => 'F58C6D24-8D10-4573-9826-65D42B8B83AD',
            'parkCode' => 'yell',
            'name' => 'Yellowstone',
            'fullName' => 'Yellowstone National Park',
            'designation' => 'National Park',
            'description' => 'On March 1, 1872, Yellowstone became the first national park.',
            'latitude' => '44.59824417',
            'longitude' => '-110.5471695',
            'states' => 'ID,MT,WY',
            'url' => 'https://www.nps.gov/yell/index.htm',
            'directionsInfo' => 'Yellowstone covers nearly 3,500 square miles.',
            'directionsUrl' => 'http://www.nps.gov/yell/planyourvisit/directions.htm',
            'weatherInfo' => 'Weather can vary even in a single day.',
            'activities' => [
                ['id' => 'BFF8C027-7C8F-480B-A5F8-CD8CE490BFBA', 'name' => 'Hiking'],
                ['id' => 'A59947B7-3376-49B4-AD02-C0423E08C5F7', 'name' => 'Camping'],
            ],
            'topics' => [
                ['id' => '69693007-2DF2-4EDE-BB3B-A25EBA72BDF5', 'name' => 'Geology'],
            ],
            'addresses' => [],
            'contacts' => [],
            'entranceFees' => [
                ['cost' => '35.00', 'title' => 'Entrance - Private Vehicle', 'description' => '7-day pass.'],
            ],
            'entrancePasses' => [
                ['cost' => '70.00', 'title' => 'Annual Entrance - Park', 'description' => 'Yearly pass.'],
            ],
            'operatingHours' => [],
            'images' => [
                ['url' => 'https://example.com/grand-prismatic.jpg', 'title' => 'Grand Prismatic Spring', 'altText' => 'Hot spring', 'caption' => '', 'credit' => 'NPS/Jim Peaco'],
            ],
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    public static function yosemite(array $overrides = []): ParkData
    {
        return ParkData::fromArray(array_replace([
            'id' => '95B9F49A-50B0-4533-8B3F-7CE9F3FD7C18',
            'parkCode' => 'yose',
            'name' => 'Yosemite',
            'fullName' => 'Yosemite National Park',
            'designation' => 'National Park',
            'description' => 'Granite cliffs, waterfalls, and giant sequoias.',
            'latitude' => '37.84883288',
            'longitude' => '-119.5571873',
            'states' => 'CA',
            'url' => 'https://www.nps.gov/yose/index.htm',
        ], $overrides));
    }

    /**
     * A National Monument unit — useful for testing the canonical
     * designation filter rejects non-NP designations.
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function devilsTower(array $overrides = []): ParkData
    {
        return ParkData::fromArray(array_replace([
            'id' => 'DEFCAD92-2104-4D2B-BDF2-65D6D14C29D7',
            'parkCode' => 'deto',
            'name' => 'Devils Tower',
            'fullName' => 'Devils Tower National Monument',
            'designation' => 'National Monument',
            'description' => 'A geological wonder.',
            'latitude' => '44.59078595',
            'longitude' => '-104.7158475',
            'states' => 'WY',
            'url' => 'https://www.nps.gov/deto/index.htm',
        ], $overrides));
    }

    /**
     * Sequoia & Kings Canyon — NPS treats this as one unit (`seki`) but our
     * canonical list treats it as two. Use for split-park tests.
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function sequoiaKingsCanyon(array $overrides = []): ParkData
    {
        return ParkData::fromArray(array_replace([
            'id' => 'FDFE5C75-F2EE-44B6-90BD-D5BC36296F8B',
            'parkCode' => 'seki',
            'name' => 'Sequoia & Kings Canyon',
            'fullName' => 'Sequoia & Kings Canyon National Parks',
            'designation' => 'National Parks',
            'description' => 'Giant sequoia groves and deep canyons.',
            'latitude' => '36.4863668',
            'longitude' => '-118.5658569',
            'states' => 'CA',
            'url' => 'https://www.nps.gov/seki/index.htm',
        ], $overrides));
    }

    /**
     * Minimal ParkData with sensible defaults — use when the test only cares
     * about a couple of fields.
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function make(array $overrides = []): ParkData
    {
        return ParkData::fromArray(array_replace([
            'id' => '00000000-0000-0000-0000-000000000001',
            'parkCode' => 'test',
            'name' => 'Test Park',
            'fullName' => 'Test National Park',
            'designation' => 'National Park',
            'description' => 'A fixture park.',
            'latitude' => '0',
            'longitude' => '0',
            'states' => 'CA',
            'url' => 'https://example.com/test',
        ], $overrides));
    }
}
