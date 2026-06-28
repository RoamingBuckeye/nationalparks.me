<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Nps\UpsertPark;
use App\Actions\Nps\UpsertPointOfInterest;
use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Enums\NpsEntity;
use App\Integrations\Nps\Enums\PoiKind;
use App\Models\NpsSync;
use App\Models\Park;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class NpsSyncCommand extends Command
{
    protected $signature = 'nps:sync
                            {entity=all : One of parks, pois, all}
                            {--park-code= : Limit to a single park (required for pois)}';

    protected $description = 'Mirror NPS data (parks and points of interest) into the local database';

    public function handle(NpsClient $client, UpsertPark $upsertPark, UpsertPointOfInterest $upsertPoi): int
    {
        $entity = (string) $this->argument('entity');
        $parkCode = $this->option('park-code');

        return match ($entity) {
            'parks' => $this->syncParks($client, $upsertPark, $parkCode),
            'pois' => $this->syncPois($client, $upsertPoi, $parkCode),
            'all' => $this->syncAll($client, $upsertPark, $upsertPoi, $parkCode),
            default => $this->unknownEntity($entity),
        };
    }

    protected function syncAll(NpsClient $client, UpsertPark $upsertPark, UpsertPointOfInterest $upsertPoi, ?string $parkCode): int
    {
        $parksResult = $this->syncParks($client, $upsertPark, $parkCode);
        if ($parksResult !== self::SUCCESS) {
            return $parksResult;
        }

        return $this->syncPois($client, $upsertPoi, $parkCode);
    }

    protected function syncParks(NpsClient $client, UpsertPark $upsertPark, ?string $parkCode): int
    {
        $codes = $parkCode !== null ? [$parkCode] : null;
        $sync = $this->openSync(NpsEntity::Parks, $parkCode);
        $count = 0;

        try {
            $this->info('Syncing parks'.($parkCode ? " (parkCode={$parkCode})" : '').'...');

            $client->parks($codes)->each(function ($parkData) use ($upsertPark, &$count): void {
                $upsertPark($parkData);
                $count++;
                $this->line("  ✓ {$parkData->fullName} ({$parkData->parkCode})");
            });

            $this->closeSync($sync, $count, succeeded: true);
            $this->info("Synced {$count} park(s).");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->closeSync($sync, $count, succeeded: false, error: $e->getMessage());
            $this->error("Sync failed after {$count} record(s): {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function syncPois(NpsClient $client, UpsertPointOfInterest $upsertPoi, ?string $parkCode): int
    {
        $parks = $parkCode !== null
            ? Park::query()->where('park_code', $parkCode)->get()
            : Park::query()->orderBy('park_code')->get();

        if ($parks->isEmpty()) {
            $this->error('No parks found locally. Run `nps:sync parks` first.');

            return self::FAILURE;
        }

        $totalProcessed = 0;
        foreach ($parks as $park) {
            $totalProcessed += $this->syncPoisForPark($client, $upsertPoi, $park->park_code);
        }

        $this->info("Synced {$totalProcessed} POI(s) across {$parks->count()} park(s).");

        return self::SUCCESS;
    }

    protected function syncPoisForPark(NpsClient $client, UpsertPointOfInterest $upsertPoi, string $parkCode): int
    {
        $total = 0;

        foreach (PoiKind::cases() as $kind) {
            $sync = $this->openSync($kind->npsEntity(), $parkCode);
            $count = 0;

            try {
                $stream = match ($kind) {
                    PoiKind::Place => $client->places($parkCode),
                    PoiKind::ThingToDo => $client->thingsToDo($parkCode),
                    PoiKind::VisitorCenter => $client->visitorCenters($parkCode),
                    PoiKind::Campground => $client->campgrounds($parkCode),
                };

                $stream->each(function ($poiData) use ($upsertPoi, &$count): void {
                    $upsertPoi($poiData);
                    $count++;
                });

                $this->line(sprintf('  %s/%s: %d', $parkCode, $kind->value, $count));
                $this->closeSync($sync, $count, succeeded: true);
                $total += $count;
            } catch (Throwable $e) {
                $this->closeSync($sync, $count, succeeded: false, error: $e->getMessage());
                $this->warn(sprintf('  %s/%s failed after %d: %s', $parkCode, $kind->value, $count, $e->getMessage()));
            }
        }

        return $total;
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
        $this->error("Unknown entity '{$entity}'. Use one of: parks, pois, all.");

        return self::FAILURE;
    }
}
