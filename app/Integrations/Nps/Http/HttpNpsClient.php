<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Http;

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Data\AlertData;
use App\Integrations\Nps\Data\AmenityData;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Integrations\Nps\Enums\NpsEntity;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Exceptions\NpsApiException;
use App\Integrations\Nps\Exceptions\NpsAuthenticationException;
use App\Integrations\Nps\Exceptions\NpsRateLimitedException;
use App\Integrations\Nps\Exceptions\NpsResponseException;
use App\Integrations\Nps\Support\NpsConfig;
use App\Integrations\Nps\Support\NpsResponse;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;

final class HttpNpsClient implements NpsClient
{
    public function __construct(
        protected readonly HttpFactory $http,
        protected readonly NpsConfig $config,
    ) {}

    public function parks(?array $parkCodes = null): LazyCollection
    {
        $query = $parkCodes === null ? [] : ['parkCode' => implode(',', $parkCodes)];

        return $this->streamPages(NpsEntity::Parks, $query)
            ->map(static fn (array $row): ParkData => ParkData::fromArray($row));
    }

    public function park(string $parkCode): ParkData
    {
        $page = $this->request(NpsEntity::Parks, ['parkCode' => $parkCode, 'limit' => 1, 'start' => 0]);

        if ($page->data === []) {
            throw NpsResponseException::unexpectedShape(
                NpsEntity::Parks->endpoint(),
                "no park found for code '{$parkCode}'",
            );
        }

        return ParkData::fromArray($page->data[0]);
    }

    public function places(string $parkCode): LazyCollection
    {
        return $this->streamPois(NpsEntity::Places, $parkCode, PoiKind::Place);
    }

    public function thingsToDo(string $parkCode): LazyCollection
    {
        return $this->streamPois(NpsEntity::ThingsToDo, $parkCode, PoiKind::ThingToDo);
    }

    public function visitorCenters(string $parkCode): LazyCollection
    {
        return $this->streamPois(NpsEntity::VisitorCenters, $parkCode, PoiKind::VisitorCenter);
    }

    public function campgrounds(string $parkCode): LazyCollection
    {
        return $this->streamPois(NpsEntity::Campgrounds, $parkCode, PoiKind::Campground);
    }

    public function alerts(?string $parkCode = null): LazyCollection
    {
        $query = $parkCode === null ? [] : ['parkCode' => $parkCode];

        return $this->streamPages(NpsEntity::Alerts, $query)
            ->map(static fn (array $row): AlertData => AlertData::fromArray($row));
    }

    public function amenities(): LazyCollection
    {
        return $this->streamPages(NpsEntity::Amenities)
            ->map(static fn (array $row): AmenityData => AmenityData::fromArray($row));
    }

    /** @return LazyCollection<int, PointOfInterestData> */
    protected function streamPois(NpsEntity $entity, string $parkCode, PoiKind $kind): LazyCollection
    {
        return $this->streamPages($entity, ['parkCode' => $parkCode])
            ->map(static fn (array $row): PointOfInterestData => PointOfInterestData::fromArray($row, $kind));
    }

    /**
     * @param  array<string, scalar>  $query
     * @return LazyCollection<int, array<string, mixed>>
     */
    protected function streamPages(NpsEntity $entity, array $query = []): LazyCollection
    {
        return LazyCollection::make(function () use ($entity, $query) {
            $start = 0;
            do {
                $page = $this->request($entity, [...$query, 'limit' => $this->config->pageSize, 'start' => $start]);
                foreach ($page->data as $row) {
                    yield $row;
                }
                $start = $page->nextStart();
            } while ($page->hasMore() && $page->data !== []);
        });
    }

    /** @param array<string, scalar> $query */
    protected function request(NpsEntity $entity, array $query): NpsResponse
    {
        $endpoint = $entity->endpoint();

        $response = $this->http
            ->withHeaders(['X-Api-Key' => $this->config->apiKey])
            ->timeout($this->config->timeout)
            ->connectTimeout($this->config->connectTimeout)
            ->retry($this->config->retries, $this->config->retryDelayMs, throw: false)
            ->acceptJson()
            ->get($this->config->baseUrl.$endpoint, $query);

        $this->guardResponse($response, $endpoint);

        $body = $response->json();
        if (! is_array($body) || ! array_key_exists('data', $body) || ! is_array($body['data'])) {
            throw NpsResponseException::unexpectedShape($endpoint, 'response is missing data[] array');
        }

        return new NpsResponse(
            total: (int) ($body['total'] ?? count($body['data'])),
            start: (int) ($body['start'] ?? 0),
            limit: (int) ($body['limit'] ?? count($body['data'])),
            data: array_values(array_filter($body['data'], static fn (mixed $row): bool => is_array($row))),
        );
    }

    protected function guardResponse(Response $response, string $endpoint): void
    {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();

        if ($status === 401 || $status === 403) {
            throw NpsAuthenticationException::rejected($endpoint);
        }

        if ($status === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 60);
            throw new NpsRateLimitedException($retryAfter, $endpoint);
        }

        throw NpsApiException::fromResponse($status, $endpoint, $response->body());
    }
}
