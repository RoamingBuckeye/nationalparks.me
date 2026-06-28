<?php

declare(strict_types=1);

namespace Tests\Factories\Nps;

use App\Integrations\Nps\Data\AlertData;

final class AlertDataFactory
{
    /** @param array<string, mixed> $overrides */
    public static function closure(array $overrides = []): AlertData
    {
        return AlertData::fromArray(array_replace([
            'id' => 'CL05URE0-0001-0000-0000-000000000001',
            'parkCode' => 'yell',
            'category' => 'Park Closure',
            'title' => 'Lower Loop Closed',
            'description' => 'Avalanche risk; closed until further notice.',
            'url' => 'https://www.nps.gov/yell/alert/lower-loop-closed',
            'lastIndexedDate' => '2026-06-28T08:00:00Z',
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    public static function advisory(array $overrides = []): AlertData
    {
        return AlertData::fromArray(array_replace([
            'id' => 'ADV1S0RY-0002-0000-0000-000000000002',
            'parkCode' => 'yell',
            'category' => 'Caution',
            'title' => 'Bear Activity in Hayden Valley',
            'description' => 'Carry bear spray; stay on marked trails.',
            'url' => 'https://www.nps.gov/yell/alert/bear-activity',
            'lastIndexedDate' => '2026-06-28T08:00:00Z',
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    public static function make(array $overrides = []): AlertData
    {
        return AlertData::fromArray(array_replace([
            'id' => '00000000-0000-0000-0000-alert0000001',
            'parkCode' => 'test',
            'category' => 'Information',
            'title' => 'Test Alert',
            'description' => '',
            'url' => '',
        ], $overrides));
    }
}
