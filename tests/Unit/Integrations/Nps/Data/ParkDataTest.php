<?php

declare(strict_types=1);

use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Enums\AddressType;
use App\Integrations\Nps\Enums\ContactKind;
use App\Integrations\Nps\Enums\FeeKind;

it('parses a park response, normalising NPS string-encoded numerics and CSV states', function () {
    $row = [
        'id' => 'F58C6D24-8D10-4573-9826-65D42B8B83AD',
        'parkCode' => 'yell',
        'name' => 'Yellowstone',
        'fullName' => 'Yellowstone National Park',
        'designation' => 'National Park',
        'description' => 'On March 1, 1872...',
        'latitude' => '44.59824417',
        'longitude' => '-110.5471695',
        'states' => 'ID,MT,WY',
        'url' => 'https://www.nps.gov/yell/',
        'directionsInfo' => 'Take the road.',
        'directionsUrl' => 'https://www.nps.gov/yell/directions',
        'weatherInfo' => 'Variable.',
        'activities' => [['id' => 'a-1', 'name' => 'Hiking'], ['id' => 'a-2', 'name' => 'Camping']],
        'topics' => [['id' => 't-1', 'name' => 'Geology']],
        'images' => [
            ['url' => 'https://example.com/p1.jpg', 'title' => 'Geyser', 'altText' => 'a geyser', 'caption' => '', 'credit' => 'NPS'],
        ],
        'addresses' => [
            ['type' => 'Physical', 'line1' => '2 Officers Row', 'line2' => '', 'line3' => '', 'city' => 'Yellowstone NP', 'stateCode' => 'WY', 'postalCode' => '82190', 'countryCode' => 'US'],
        ],
        'contacts' => [
            'phoneNumbers' => [['phoneNumber' => '307-555-0100', 'type' => 'Voice']],
            'emailAddresses' => [['emailAddress' => 'park@nps.gov', 'description' => 'General']],
        ],
        'entranceFees' => [['cost' => '35.00', 'title' => 'Private Vehicle', 'description' => '7-day pass']],
        'entrancePasses' => [['cost' => '70.00', 'title' => 'Annual', 'description' => 'Annual pass']],
        'operatingHours' => [['name' => 'Park Hours', 'description' => 'Open year-round', 'standardHours' => ['monday' => 'All Day'], 'exceptions' => []]],
    ];

    $park = ParkData::fromArray($row);

    expect($park->npsId)->toBe($row['id'])
        ->and($park->parkCode)->toBe('yell')
        ->and($park->latitude)->toBe(44.59824417)
        ->and($park->longitude)->toBe(-110.5471695)
        ->and($park->states)->toBe(['ID', 'MT', 'WY'])
        ->and($park->activities)->toBe(['Hiking', 'Camping'])
        ->and($park->topics)->toBe(['Geology'])
        ->and($park->images)->toHaveCount(1)
        ->and($park->images[0]->credit)->toBe('NPS')
        ->and($park->images[0]->caption)->toBeNull()
        ->and($park->addresses[0]->type)->toBe(AddressType::Physical)
        ->and($park->contacts)->toHaveCount(2)
        ->and($park->contacts[0]->kind)->toBe(ContactKind::Phone)
        ->and($park->contacts[0]->value)->toBe('307-555-0100')
        ->and($park->contacts[1]->kind)->toBe(ContactKind::Email)
        ->and($park->fees)->toHaveCount(2)
        ->and($park->fees[0]->kind)->toBe(FeeKind::Entrance)
        ->and($park->fees[0]->cost)->toBe(35.00)
        ->and($park->fees[1]->kind)->toBe(FeeKind::Pass)
        ->and($park->fees[1]->cost)->toBe(70.00)
        ->and($park->operatingHours[0]->standardHours)->toBe(['monday' => 'All Day']);
});

it('returns null lat/long when NPS gives empty strings', function () {
    $park = ParkData::fromArray([
        'id' => 'x', 'parkCode' => 'abcd', 'name' => 'X', 'fullName' => 'X', 'designation' => 'NP', 'description' => '',
        'latitude' => '', 'longitude' => '', 'states' => '', 'url' => '',
    ]);

    expect($park->latitude)->toBeNull()->and($park->longitude)->toBeNull()->and($park->states)->toBe([]);
});
