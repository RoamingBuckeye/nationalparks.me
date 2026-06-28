<?php

declare(strict_types=1);

use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\PoiKind;

it('parses a /places row and pulls parkCode from relatedParks', function () {
    $row = [
        'id' => '6DD2F4BE-2B7E-498D-9617-6246F5E8CF92',
        'title' => 'A Wildlife Paradise',
        'url' => 'https://www.nps.gov/places/000/a-wildlife-paradise.htm',
        'latitude' => '44.960129',
        'longitude' => '-110.567181',
        'bodyText' => '<p>Many of the park\'s ungulates...</p>',
        'listingDescription' => '',
        'tags' => ['wildlife', 'elk'],
        'amenities' => ['Restrooms', 'Parking'],
        'images' => [['url' => 'https://example.com/wildlife.jpg', 'title' => 'Elk', 'altText' => 'elk', 'caption' => '', 'credit' => 'NPS']],
        'relatedParks' => [['parkCode' => 'yell', 'fullName' => 'Yellowstone NP', 'name' => 'Yellowstone', 'designation' => 'National Park', 'url' => '', 'states' => 'WY']],
        'isPassportStampLocation' => '0',
        'isOpenToPublic' => '1',
    ];

    $poi = PointOfInterestData::fromArray($row, PoiKind::Place);

    expect($poi->npsId)->toBe($row['id'])
        ->and($poi->kind)->toBe(PoiKind::Place)
        ->and($poi->title)->toBe('A Wildlife Paradise')
        ->and($poi->parkCode)->toBe('yell')
        ->and($poi->latitude)->toBe(44.960129)
        ->and($poi->longitude)->toBe(-110.567181)
        ->and($poi->description)->toContain('ungulates')
        ->and($poi->tags)->toBe(['wildlife', 'elk'])
        ->and($poi->amenities)->toBe(['Restrooms', 'Parking'])
        ->and($poi->isPassportStampLocation)->toBeFalse()
        ->and($poi->details)->toHaveKey('isOpenToPublic')
        ->and($poi->details['isOpenToPublic'])->toBeTrue();
});

it('normalises amenity objects from /visitorcenters into a flat string list', function () {
    $row = [
        'id' => 'vc-1', 'name' => 'Albright VC', 'parkCode' => 'yell',
        'latitude' => '44.97', 'longitude' => '-110.69',
        'amenities' => [['name' => 'Restrooms'], ['name' => 'Bookstore']],
    ];

    $poi = PointOfInterestData::fromArray($row, PoiKind::VisitorCenter);

    expect($poi->title)->toBe('Albright VC')
        ->and($poi->amenities)->toBe(['Restrooms', 'Bookstore']);
});

it('captures campground-specific details', function () {
    $row = [
        'id' => 'cg-1', 'name' => 'Mammoth Campground', 'parkCode' => 'yell',
        'numberOfSitesReservable' => '85', 'numberOfSitesFirstComeFirstServe' => '0',
        'reservationUrl' => 'https://reserve.example.com',
    ];

    $poi = PointOfInterestData::fromArray($row, PoiKind::Campground);

    expect($poi->details['numberOfSitesReservable'])->toBe(85)
        ->and($poi->details['numberOfSitesFirstComeFirstServe'])->toBe(0)
        ->and($poi->details['reservationUrl'])->toBe('https://reserve.example.com');
});
