<?php

declare(strict_types=1);

namespace App\Actions\Nps;

use App\Integrations\Nps\Data\ImageData;
use App\Integrations\Nps\Data\ParkData;
use App\Models\Park;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class UpsertPark
{
    /**
     * Upsert a park, keyed on park_code (since split children share their
     * source's nps_id). For split children, pass $sourceCode / $sourceId
     * so we can map back to the original NPS unit.
     */
    public function __invoke(ParkData $data, ?string $sourceCode = null, ?string $sourceId = null): Park
    {
        return DB::transaction(function () use ($data, $sourceCode, $sourceId): Park {
            $park = Park::updateOrCreate(
                ['park_code' => $data->parkCode],
                [
                    'nps_id' => $data->npsId,
                    'nps_source_code' => $sourceCode,
                    'nps_source_id' => $sourceId,
                    'name' => $data->name,
                    'full_name' => $data->fullName,
                    'designation' => $data->designation,
                    'description' => $data->description,
                    'url' => $data->url,
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                    'states' => $data->states,
                    'directions_info' => $data->directionsInfo,
                    'directions_url' => $data->directionsUrl,
                    'weather_info' => $data->weatherInfo,
                    'last_synced_at' => now(),
                    'archived_at' => null,
                ],
            );

            $this->syncImages($park->images(), $data->images);

            return $park->refresh();
        });
    }

    /** @param list<ImageData> $images */
    protected function syncImages(MorphMany $relation, array $images): void
    {
        $seenUrls = [];
        foreach ($images as $index => $image) {
            if ($image->url === '' || isset($seenUrls[$image->url])) {
                continue;
            }
            $seenUrls[$image->url] = true;

            $relation->updateOrCreate(
                ['url' => $image->url],
                [
                    'title' => $image->title,
                    'alt_text' => $image->altText,
                    'caption' => $image->caption,
                    'credit' => $image->credit,
                    'sort_order' => $index,
                ],
            );
        }

        if ($seenUrls !== []) {
            $relation->whereNotIn('url', array_keys($seenUrls))->delete();
        }
    }
}
