<?php

declare(strict_types=1);

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Exceptions\NpsApiException;
use App\Integrations\Nps\Exceptions\NpsAuthenticationException;
use App\Integrations\Nps\Exceptions\NpsRateLimitedException;
use App\Integrations\Nps\Http\HttpNpsClient;
use App\Integrations\Nps\Support\NpsConfig;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

function makeClient(): HttpNpsClient
{
    return new HttpNpsClient(
        app(HttpFactory::class),
        new NpsConfig(
            apiKey: 'test-key',
            baseUrl: 'https://developer.nps.gov/api/v1/',
            timeout: 5,
            connectTimeout: 2,
            retries: 1,
            retryDelayMs: 10,
            pageSize: 2,
        ),
    );
}

beforeEach(function () {
    Http::preventStrayRequests();
    // The HttpNpsClient singleton fail-fasts on a missing API key; CI has no
    // real key so set a dummy before any container resolution in this file.
    config(['services.nps.key' => 'test-key']);
});

it('paginates parks until total is reached', function () {
    Http::fake([
        'developer.nps.gov/api/v1/parks?*start=0*' => Http::response([
            'total' => '3', 'limit' => '2', 'start' => '0',
            'data' => [
                ['id' => 'p1', 'parkCode' => 'aaaa', 'name' => 'A', 'fullName' => 'A NP', 'designation' => 'NP', 'description' => '', 'latitude' => '1', 'longitude' => '2', 'states' => 'WY', 'url' => ''],
                ['id' => 'p2', 'parkCode' => 'bbbb', 'name' => 'B', 'fullName' => 'B NP', 'designation' => 'NP', 'description' => '', 'latitude' => '1', 'longitude' => '2', 'states' => 'WY', 'url' => ''],
            ],
        ]),
        'developer.nps.gov/api/v1/parks?*start=2*' => Http::response([
            'total' => '3', 'limit' => '2', 'start' => '2',
            'data' => [
                ['id' => 'p3', 'parkCode' => 'cccc', 'name' => 'C', 'fullName' => 'C NP', 'designation' => 'NP', 'description' => '', 'latitude' => '1', 'longitude' => '2', 'states' => 'WY', 'url' => ''],
            ],
        ]),
    ]);

    $codes = makeClient()->parks()->map(fn ($p) => $p->parkCode)->all();

    expect($codes)->toBe(['aaaa', 'bbbb', 'cccc']);
});

it('returns a single park via park()', function () {
    Http::fake([
        'developer.nps.gov/api/v1/parks*' => Http::response([
            'total' => '1', 'limit' => '1', 'start' => '0',
            'data' => [['id' => 'p1', 'parkCode' => 'yell', 'name' => 'Yellowstone', 'fullName' => 'Yellowstone NP', 'designation' => 'NP', 'description' => '', 'latitude' => '44', 'longitude' => '-110', 'states' => 'WY', 'url' => '']],
        ]),
    ]);

    expect(makeClient()->park('yell')->parkCode)->toBe('yell');
});

it('maps /places rows into PointOfInterestData with kind=Place', function () {
    Http::fake([
        'developer.nps.gov/api/v1/places*' => Http::response([
            'total' => '1', 'limit' => '2', 'start' => '0',
            'data' => [['id' => 'pl1', 'title' => 'Old Faithful', 'parkCode' => 'yell', 'latitude' => '44.46', 'longitude' => '-110.83', 'bodyText' => '<p>Geyser.</p>']],
        ]),
    ]);

    $places = makeClient()->places('yell')->all();

    expect($places)->toHaveCount(1)
        ->and($places[0]->kind)->toBe(PoiKind::Place)
        ->and($places[0]->title)->toBe('Old Faithful');
});

it('throws NpsAuthenticationException on 403', function () {
    Http::fake([
        'developer.nps.gov/api/v1/parks*' => Http::response(['error' => ['code' => 'API_KEY_INVALID']], 403),
    ]);

    makeClient()->parks()->all();
})->throws(NpsAuthenticationException::class);

it('throws NpsRateLimitedException on 429 carrying Retry-After', function () {
    Http::fake([
        'developer.nps.gov/api/v1/parks*' => Http::response('Too many requests', 429, ['Retry-After' => '42']),
    ]);

    try {
        makeClient()->parks()->all();
        $this->fail('Expected NpsRateLimitedException');
    } catch (NpsRateLimitedException $e) {
        expect($e->retryAfterSeconds)->toBe(42);
    }
});

it('throws NpsApiException on 5xx', function () {
    Http::fake([
        'developer.nps.gov/api/v1/parks*' => Http::response('oops', 503),
    ]);

    makeClient()->parks()->all();
})->throws(NpsApiException::class);

it('binds NpsClient to HttpNpsClient via the service container', function () {
    expect(app(NpsClient::class))->toBeInstanceOf(HttpNpsClient::class);
});
