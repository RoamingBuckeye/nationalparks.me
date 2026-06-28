<?php

declare(strict_types=1);

namespace App\Actions\Nps;

use App\Integrations\Nps\Data\ImageData;
use App\Integrations\Nps\Data\OperatingHoursData;
use App\Integrations\Nps\Data\PointOfInterestData;
use App\Models\Activity;
use App\Models\Amenity;
use App\Models\Image;
use App\Models\Park;
use App\Models\PointOfInterest;
use App\Models\Tag;
use App\Models\Topic;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpsertPointOfInterest
{
    /**
     * Upsert a POI. Keyed on (nps_id, park_id) — a single NPS POI may attach
     * to multiple parks (e.g. seki POIs duplicate across sequ + kica).
     * Pass $parkCodeOverride when the resolved park differs from $data->parkCode.
     */
    public function __invoke(PointOfInterestData $data, ?string $parkCodeOverride = null): PointOfInterest
    {
        $parkCode = $parkCodeOverride ?? $data->parkCode;
        $park = $parkCode === null
            ? null
            : Park::query()->where('park_code', $parkCode)->first();

        if ($park === null) {
            throw (new ModelNotFoundException)->setModel(Park::class, [$parkCode ?? '(null)']);
        }

        return DB::transaction(function () use ($data, $park): PointOfInterest {
            $poi = PointOfInterest::updateOrCreate(
                ['nps_id' => $data->npsId, 'park_id' => $park->id],
                [
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
                    'operating_hours' => self::serializeOperatingHours($data->operatingHours),
                    'last_synced_at' => now(),
                    'archived_at' => null,
                ],
            );

            $this->syncImages($poi->images(), $data->images);

            $poi->activities()->sync($this->activityIds($data->activities));
            $poi->topics()->sync($this->topicIds($data->topics));
            $poi->tags()->sync($this->tagIds($data->tags));
            $poi->amenities()->sync($this->amenityIds($data->amenities));

            return $poi->refresh();
        });
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    protected function activityIds(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $trimmed = trim($name);
            if ($trimmed === '') {
                continue;
            }
            $ids[Activity::firstOrCreate(['slug' => Str::slug($trimmed)], ['name' => $trimmed])->id] = true;
        }

        return array_keys($ids);
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    protected function topicIds(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $trimmed = trim($name);
            if ($trimmed === '') {
                continue;
            }
            $ids[Topic::firstOrCreate(['slug' => Str::slug($trimmed)], ['name' => $trimmed])->id] = true;
        }

        return array_keys($ids);
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    protected function tagIds(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $trimmed = trim($name);
            if ($trimmed === '') {
                continue;
            }
            $ids[Tag::firstOrCreate(['slug' => Str::slug($trimmed)], ['name' => $trimmed])->id] = true;
        }

        return array_keys($ids);
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    protected function amenityIds(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $trimmed = trim($name);
            if ($trimmed === '') {
                continue;
            }
            $ids[Amenity::firstOrCreate(['slug' => Str::slug($trimmed)], ['name' => $trimmed])->id] = true;
        }

        return array_keys($ids);
    }

    /**
     * @param  list<OperatingHoursData>  $hours
     * @return list<array<string, mixed>>
     */
    protected static function serializeOperatingHours(array $hours): array
    {
        return array_map(static fn (OperatingHoursData $h): array => [
            'name' => $h->name,
            'description' => $h->description,
            'standard_hours' => $h->standardHours,
            'exceptions' => $h->exceptions,
        ], $hours);
    }

    /**
     * @param  MorphMany<Image, PointOfInterest>  $relation
     * @param  list<ImageData>  $images
     */
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
