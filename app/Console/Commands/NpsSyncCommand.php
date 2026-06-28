<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Nps\UpsertAlert;
use App\Actions\Nps\UpsertPark;
use App\Actions\Nps\UpsertPointOfInterest;
use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Data\ParkData;
use App\Integrations\Nps\Enums\NpsEntity;
use App\Integrations\Nps\Enums\PoiKind;
use App\Integrations\Nps\Exceptions\NpsRateLimitedException;
use App\Models\Alert;
use App\Models\NpsSync;
use App\Models\Park;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Throwable;

class NpsSyncCommand extends Command
{
    public const string CANONICAL = 'canonical';

    public const string ALL_DESIGNATIONS = 'all';

    /** NPS designations counted as one of the canonical "63 National Parks". */
    public const array CANONICAL_DESIGNATIONS = [
        'National Park',
        'National Park & Preserve',
        'National and State Parks',
    ];

    /** Park codes counted as canonical despite having a non-matching designation. */
    public const array CANONICAL_EXTRA_PARK_CODES = [
        'npsa', // National Park of American Samoa — empty designation upstream
    ];

    /**
     * NPS units we split into multiple local parks. Map of upstream park_code
     * to a list of child {code, name, fullName}. Children share the source's
     * lat/long, description, and images; back-references live in nps_source_*.
     *
     * @var array<string, list<array{code: string, name: string, fullName: string}>>
     */
    public const array SPLIT_PARKS = [
        'seki' => [
            ['code' => 'sequ', 'name' => 'Sequoia', 'fullName' => 'Sequoia National Park'],
            ['code' => 'kica', 'name' => 'Kings Canyon', 'fullName' => 'Kings Canyon National Park'],
        ],
    ];

    protected const int RATE_LIMIT_MAX_ATTEMPTS = 3;

    protected $signature = 'nps:sync
                            {entity=all : One of parks, pois, alerts, all}
                            {--park-code= : Limit to a single park}
                            {--designation=canonical : "canonical" (default), "all", or an exact NPS designation string}';

    protected $description = 'Mirror NPS data (parks, points of interest, alerts) into the local database';

    public function handle(NpsClient $client, UpsertPark $upsertPark, UpsertPointOfInterest $upsertPoi, UpsertAlert $upsertAlert): int
    {
        $entity = (string) $this->argument('entity');
        $parkCode = $this->option('park-code');
        $designation = $this->resolvedDesignation();

        return match ($entity) {
            'parks' => $this->syncParks($client, $upsertPark, $parkCode, $designation),
            'pois' => $this->syncPois($client, $upsertPoi, $parkCode, $designation),
            'alerts' => $this->syncAlerts($client, $upsertAlert, $parkCode),
            'all' => $this->syncAll($client, $upsertPark, $upsertPoi, $parkCode, $designation),
            default => $this->unknownEntity($entity),
        };
    }

    protected function syncAll(NpsClient $client, UpsertPark $upsertPark, UpsertPointOfInterest $upsertPoi, ?string $parkCode, ?string $designation): int
    {
        $parksResult = $this->syncParks($client, $upsertPark, $parkCode, $designation);
        if ($parksResult !== self::SUCCESS) {
            return $parksResult;
        }

        return $this->syncPois($client, $upsertPoi, $parkCode, $designation);
    }

    protected function syncParks(NpsClient $client, UpsertPark $upsertPark, ?string $parkCode, ?string $designation): int
    {
        $codes = $parkCode !== null ? [$parkCode] : null;
        $sync = $this->openSync(NpsEntity::Parks, $parkCode);
        $count = 0;
        $skipped = 0;
        $label = $designation ?? self::ALL_DESIGNATIONS;

        try {
            $this->info('Syncing parks ['.$label.']'.($parkCode ? " (parkCode={$parkCode})" : '').'...');

            $this->withRateLimitRetry(function () use ($client, $codes, $designation, $upsertPark, &$count, &$skipped): void {
                $count = 0;
                $skipped = 0;
                $client->parks($codes)->each(function ($parkData) use ($designation, $upsertPark, &$count, &$skipped): void {
                    if (! $this->shouldInclude($parkData, $designation)) {
                        $skipped++;

                        return;
                    }

                    if (array_key_exists($parkData->parkCode, self::SPLIT_PARKS)) {
                        $count += $this->upsertSplitChildren($parkData, $upsertPark);

                        return;
                    }

                    $upsertPark($parkData);
                    $count++;
                    $this->line("  ✓ {$parkData->fullName} ({$parkData->parkCode})");
                });
            });

            $this->closeSync($sync, $count, succeeded: true);
            $this->info("Synced {$count} park(s); skipped {$skipped} non-matching unit(s).");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->closeSync($sync, $count, succeeded: false, error: $e->getMessage());
            $this->error("Sync failed after {$count} record(s): {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Produce two (or more) synthetic ParkData rows from a single NPS unit
     * for parks in SPLIT_PARKS. Each child carries nps_source_code back to
     * the upstream code so we can re-fetch its POIs.
     */
    protected function upsertSplitChildren(ParkData $sourceData, UpsertPark $upsertPark): int
    {
        $count = 0;
        foreach (self::SPLIT_PARKS[$sourceData->parkCode] as $child) {
            $childData = new ParkData(
                npsId: $sourceData->npsId,
                parkCode: $child['code'],
                name: $child['name'],
                fullName: $child['fullName'],
                designation: 'National Park',
                description: $sourceData->description,
                latitude: $sourceData->latitude,
                longitude: $sourceData->longitude,
                states: $sourceData->states,
                url: $sourceData->url,
                directionsInfo: $sourceData->directionsInfo,
                directionsUrl: $sourceData->directionsUrl,
                weatherInfo: $sourceData->weatherInfo,
                activities: $sourceData->activities,
                topics: $sourceData->topics,
                images: $sourceData->images,
                addresses: $sourceData->addresses,
                contacts: $sourceData->contacts,
                fees: $sourceData->fees,
                operatingHours: $sourceData->operatingHours,
            );

            $upsertPark($childData, sourceCode: $sourceData->parkCode, sourceId: $sourceData->npsId);
            $count++;
            $this->line("  ✓ {$child['fullName']} ({$child['code']}, split from {$sourceData->parkCode})");
        }

        return $count;
    }

    protected function syncPois(NpsClient $client, UpsertPointOfInterest $upsertPoi, ?string $parkCode, ?string $designation): int
    {
        $parks = $this->parksInScope($parkCode, $designation);

        if ($parks->isEmpty()) {
            $hint = $designation !== null
                ? "No parks matching designation '{$designation}' found locally. Run `nps:sync parks` first or pass `--designation=all`."
                : 'No parks found locally. Run `nps:sync parks` first.';
            $this->error($hint);

            return self::FAILURE;
        }

        $totalProcessed = 0;
        foreach ($parks as $park) {
            $totalProcessed += $this->syncPoisForPark($client, $upsertPoi, $park);
        }

        $this->info("Synced {$totalProcessed} POI(s) across {$parks->count()} park(s).");

        return self::SUCCESS;
    }

    /** @return Collection<int, Park> */
    protected function parksInScope(?string $parkCode, ?string $designation): Collection
    {
        $query = Park::query()->orderBy('park_code');

        if ($parkCode !== null) {
            return $query->where('park_code', $parkCode)->get();
        }

        if ($designation === null) {
            return $query->get();
        }

        if ($designation === self::CANONICAL) {
            return $query->where(function ($q): void {
                $q->whereIn('designation', self::CANONICAL_DESIGNATIONS)
                    ->orWhereIn('park_code', self::CANONICAL_EXTRA_PARK_CODES)
                    ->orWhereNotNull('nps_source_code');
            })->get();
        }

        return $query->where('designation', $designation)->get();
    }

    protected function syncPoisForPark(NpsClient $client, UpsertPointOfInterest $upsertPoi, Park $park): int
    {
        $sourceCode = $park->npsSourceCode();
        $targetCode = $park->park_code;
        $total = 0;

        foreach (PoiKind::cases() as $kind) {
            $sync = $this->openSync($kind->npsEntity(), $targetCode);
            $count = 0;

            try {
                $this->withRateLimitRetry(function () use ($client, $kind, $sourceCode, $targetCode, $upsertPoi, &$count): void {
                    $count = 0;
                    $this->streamFor($client, $kind, $sourceCode)->each(function ($poiData) use ($targetCode, $upsertPoi, &$count): void {
                        $upsertPoi($poiData, parkCodeOverride: $targetCode);
                        $count++;
                    });
                });

                $this->line(sprintf('  %s/%s: %d%s', $targetCode, $kind->value, $count, $park->isSplitChild() ? " (from {$sourceCode})" : ''));
                $this->closeSync($sync, $count, succeeded: true);
                $total += $count;
            } catch (Throwable $e) {
                $this->closeSync($sync, $count, succeeded: false, error: $e->getMessage());
                $this->warn(sprintf('  %s/%s failed after %d: %s', $targetCode, $kind->value, $count, $e->getMessage()));
            }
        }

        return $total;
    }

    protected function syncAlerts(NpsClient $client, UpsertAlert $upsertAlert, ?string $parkCode): int
    {
        $sync = $this->openSync(NpsEntity::Alerts, $parkCode);
        $count = 0;
        $skipped = 0;
        $syncStartedAt = Carbon::now();

        // Build a parkCode → list<target parkCodes> map. For split parents (e.g. seki),
        // upstream alerts map to the children (sequ, kica). Non-canonical codes are absent
        // and get skipped.
        $codeMap = $this->buildAlertParkCodeMap();

        try {
            $this->info('Syncing alerts'.($parkCode ? " (parkCode={$parkCode})" : '').'...');

            $this->withRateLimitRetry(function () use ($client, $parkCode, $codeMap, $upsertAlert, &$count, &$skipped): void {
                $count = 0;
                $skipped = 0;
                $client->alerts($parkCode)->each(function ($alertData) use ($codeMap, $upsertAlert, &$count, &$skipped): void {
                    $upstream = $alertData->parkCode;
                    if ($upstream === null || ! isset($codeMap[$upstream])) {
                        $skipped++;

                        return;
                    }
                    foreach ($codeMap[$upstream] as $targetCode) {
                        $alert = $upsertAlert($alertData, parkCodeOverride: $targetCode);
                        if ($alert !== null) {
                            $count++;
                        }
                    }
                });
            });

            $deleted = $this->pruneStaleAlerts($syncStartedAt, $parkCode);

            $this->closeSync($sync, $count, succeeded: true);
            $this->info("Synced {$count} alert(s); skipped {$skipped} for non-canonical parks; pruned {$deleted} stale.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->closeSync($sync, $count, succeeded: false, error: $e->getMessage());
            $this->error("Alert sync failed after {$count} record(s): {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Build a map of upstream parkCode → list of local parkCodes to attach.
     * Most entries are 1:1 (own code). Split parents map to all children.
     *
     * @return array<string, list<string>>
     */
    protected function buildAlertParkCodeMap(): array
    {
        $map = [];

        // Direct: every local park stores its upstream source code (or its own).
        foreach (Park::query()->get(['park_code', 'nps_source_code']) as $park) {
            $upstream = $park->nps_source_code ?? $park->park_code;
            $map[$upstream] ??= [];
            $map[$upstream][] = $park->park_code;
        }

        return $map;
    }

    protected function pruneStaleAlerts(Carbon $syncStartedAt, ?string $parkCode): int
    {
        $query = Alert::query()->where('last_synced_at', '<', $syncStartedAt);

        if ($parkCode !== null) {
            $query->where('park_code', $parkCode);
        }

        return $query->delete();
    }

    protected function streamFor(NpsClient $client, PoiKind $kind, string $parkCode)
    {
        return match ($kind) {
            PoiKind::Place => $client->places($parkCode),
            PoiKind::ThingToDo => $client->thingsToDo($parkCode),
            PoiKind::VisitorCenter => $client->visitorCenters($parkCode),
            PoiKind::Campground => $client->campgrounds($parkCode),
        };
    }

    /**
     * Run $work, catching NpsRateLimitedException to sleep and retry up to
     * RATE_LIMIT_MAX_ATTEMPTS times. Upserts are idempotent so restarting
     * a stream is safe — already-processed rows become no-ops.
     */
    protected function withRateLimitRetry(Closure $work): void
    {
        $attempt = 0;

        while (true) {
            try {
                $work();

                return;
            } catch (NpsRateLimitedException $e) {
                $attempt++;
                if ($attempt >= self::RATE_LIMIT_MAX_ATTEMPTS) {
                    throw $e;
                }
                $wait = max($e->retryAfterSeconds, 1);
                $this->warn("  rate limited; sleeping {$wait}s (attempt {$attempt}/".self::RATE_LIMIT_MAX_ATTEMPTS.')...');
                $this->sleep($wait);
            }
        }
    }

    protected function sleep(int $seconds): void
    {
        sleep($seconds);
    }

    protected function shouldInclude(ParkData $data, ?string $designation): bool
    {
        return match (true) {
            $designation === null => true,
            $designation === self::CANONICAL => in_array($data->designation, self::CANONICAL_DESIGNATIONS, true)
                || in_array($data->parkCode, self::CANONICAL_EXTRA_PARK_CODES, true)
                || array_key_exists($data->parkCode, self::SPLIT_PARKS),
            default => $data->designation === $designation,
        };
    }

    protected function resolvedDesignation(): ?string
    {
        $value = (string) $this->option('designation');

        return $value === self::ALL_DESIGNATIONS ? null : $value;
    }

    protected function openSync(NpsEntity $entity, ?string $parkCode): NpsSync
    {
        return NpsSync::create([
            'entity' => $entity,
            'park_code' => $parkCode,
            'started_at' => Carbon::now(),
            'records_processed' => 0,
        ]);
    }

    protected function closeSync(NpsSync $sync, int $count, bool $succeeded, ?string $error = null): void
    {
        $now = Carbon::now();
        $sync->update([
            'finished_at' => $now,
            'succeeded_at' => $succeeded ? $now : null,
            'records_processed' => $count,
            'last_error' => $error,
        ]);
    }

    protected function unknownEntity(string $entity): int
    {
        $this->error("Unknown entity '{$entity}'. Use one of: parks, pois, alerts, all.");

        return self::FAILURE;
    }
}
