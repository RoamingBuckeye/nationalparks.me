<?php

declare(strict_types=1);

namespace App\Actions\Nps;

use App\Integrations\Nps\Data\ImageData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Models\Park;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class UpsertPointOfInterest
{
    public function __invoke(PointOfInterestData $data): PointOfInterest
    {
        $park = Park::query()->where('park_code', $data->parkCode)->first();

        if ($park === null) {
            throw (new ModelNotFoundException)->setModel(Park::class, [$data->parkCode]);
        }

        return DB::transaction(function () use ($data, $park): PointOfInterest {
            $poi = PointOfInterest::updateOrCreate(
                ['nps_id' => $data->npsId],
                [
                    'park_id' => $park->id,
                    'kind' => $data->kind,
                    'title' => $data->title,
                    'description' => $data->description,
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                    'url' => $data->url,
                    'tags' => $data->tags,
                    'amenities' => $data->amenities,
                    'is_passport_stamp_location' => $data->isPassportStampLocation,
                    'details' => $data->details,
                    'last_synced_at' => now(),
                    'archived_at' => null,
                ],
            );

            $this->syncImages($poi->images(), $data->images);

            return $poi->refresh();
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
