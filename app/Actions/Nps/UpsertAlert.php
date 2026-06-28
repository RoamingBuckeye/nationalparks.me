<?php

declare(strict_types=1);

namespace App\Actions\Nps;

use App\Integrations\Nps\Data\AlertData;
use App\Models\Alert;
use App\Models\Park;

class UpsertAlert
{
    /**
     * Upsert an alert. Returns null if the alert references a park we don't
     * track (non-canonical or missing). Pass $parkCodeOverride to attach the
     * alert to a specific park (e.g. seki alerts duplicating to sequ + kica).
     */
    public function __invoke(AlertData $data, ?string $parkCodeOverride = null): ?Alert
    {
        $parkCode = $parkCodeOverride ?? $data->parkCode;

        if ($parkCode === null) {
            return null;
        }

        $park = Park::query()->where('park_code', $parkCode)->first();

        if ($park === null) {
            return null;
        }

        return Alert::updateOrCreate(
            ['nps_id' => $data->npsId, 'park_id' => $park->id],
            [
                'park_code' => $parkCode,
                'category' => $data->category,
                'title' => $data->title,
                'description' => $data->description,
                'url' => $data->url,
                'last_indexed_at' => $data->lastIndexedAt,
                'last_synced_at' => now(),
                'archived_at' => null,
            ],
        );
    }
}
