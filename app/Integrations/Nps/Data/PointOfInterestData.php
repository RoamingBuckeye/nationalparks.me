<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

use App\Integrations\Nps\Enums\PoiKind;

final readonly class PointOfInterestData
{
    /**
     * @param  list<string>  $tags
     * @param  list<string>  $amenities
     * @param  list<ImageData>  $images
     * @param  list<RelatedParkData>  $relatedParks
     * @param  array<string, mixed>  $details
     * @param  list<OperatingHoursData>  $operatingHours
     */
    public function __construct(
        public string $npsId,
        public ?string $parkCode,
        public PoiKind $kind,
        public string $title,
        public ?string $description,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $url,
        public array $tags,
        public array $amenities,
        public array $images,
        public array $relatedParks,
        public bool $isPassportStampLocation,
        public array $details,
        public array $operatingHours = [],
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row, PoiKind $kind): self
    {
        $relatedParks = RelatedParkData::listFrom(self::asListOrNull($row['relatedParks'] ?? null));
        $parkCode = self::resolveParkCode($row, $relatedParks);

        return new self(
            npsId: (string) ($row['id'] ?? ''),
            parkCode: $parkCode,
            kind: $kind,
            title: self::resolveTitle($row, $kind),
            description: self::resolveDescription($row, $kind),
            latitude: self::nullableFloat($row['latitude'] ?? null),
            longitude: self::nullableFloat($row['longitude'] ?? null),
            url: self::nullableString($row['url'] ?? null),
            tags: self::stringList($row['tags'] ?? null),
            amenities: self::resolveAmenities($row['amenities'] ?? null),
            images: ImageData::listFrom(self::asListOrNull($row['images'] ?? null)),
            relatedParks: $relatedParks,
            isPassportStampLocation: self::nullableBool($row['isPassportStampLocation'] ?? null) ?? false,
            details: self::detailsFor($row, $kind),
            operatingHours: OperatingHoursData::listFrom(self::asListOrNull($row['operatingHours'] ?? null)),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<self>
     */
    public static function listFrom(array $rows, PoiKind $kind): array
    {
        return array_values(array_map(
            fn (array $row): self => self::fromArray($row, $kind),
            $rows,
        ));
    }

    protected static function resolveTitle(array $row, PoiKind $kind): string
    {
        return match ($kind) {
            PoiKind::VisitorCenter, PoiKind::Campground => (string) ($row['name'] ?? ''),
            default => (string) ($row['title'] ?? ''),
        };
    }

    protected static function resolveDescription(array $row, PoiKind $kind): ?string
    {
        $value = match ($kind) {
            PoiKind::Place => $row['bodyText'] ?? $row['listingDescription'] ?? null,
            PoiKind::ThingToDo => $row['longDescription'] ?? $row['shortDescription'] ?? null,
            default => $row['description'] ?? null,
        };

        return self::nullableString($value);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<RelatedParkData>  $relatedParks
     */
    protected static function resolveParkCode(array $row, array $relatedParks): ?string
    {
        $direct = self::nullableString($row['parkCode'] ?? null);
        if ($direct !== null) {
            return $direct;
        }

        return $relatedParks[0]->parkCode !== '' ? $relatedParks[0]->parkCode : null;
    }

    /** @return list<string> */
    protected static function resolveAmenities(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        // /places returns ["Restrooms", "Picnic Tables"]; /visitorcenters returns [{name: "..."}].
        $out = [];
        foreach ($value as $entry) {
            if (is_string($entry)) {
                $trimmed = trim($entry);
                if ($trimmed !== '') {
                    $out[] = $trimmed;
                }

                continue;
            }

            if (is_array($entry)) {
                $name = trim((string) ($entry['name'] ?? ''));
                if ($name !== '') {
                    $out[] = $name;
                }
            }
        }

        return $out;
    }

    /** @return array<string, mixed> */
    protected static function detailsFor(array $row, PoiKind $kind): array
    {
        return match ($kind) {
            PoiKind::Place => array_filter([
                'audioDescription' => self::nullableString($row['audioDescription'] ?? null),
                'isOpenToPublic' => self::nullableBool($row['isOpenToPublic'] ?? null),
                'isMapPinHidden' => self::nullableBool($row['isMapPinHidden'] ?? null),
                'isManagedByNps' => self::nullableBool($row['isManagedByNps'] ?? null),
                'managedByOrg' => self::nullableString($row['managedByOrg'] ?? null),
                'managedByUrl' => self::nullableString($row['managedByUrl'] ?? null),
                'quickFacts' => self::asListOrNull($row['quickFacts'] ?? null),
            ], static fn (mixed $v): bool => $v !== null),
            PoiKind::ThingToDo => array_filter([
                'shortDescription' => self::nullableString($row['shortDescription'] ?? null),
                'longDescription' => self::nullableString($row['longDescription'] ?? null),
                'duration' => self::nullableString($row['duration'] ?? null),
                'durationDescription' => self::nullableString($row['durationDescription'] ?? null),
                'season' => self::stringList($row['season'] ?? null),
                'seasonDescription' => self::nullableString($row['seasonDescription'] ?? null),
                'timeOfDay' => self::stringList($row['timeOfDay'] ?? null),
                'timeOfDayDescription' => self::nullableString($row['timeOfDayDescription'] ?? null),
                'accessibilityInformation' => self::nullableString($row['accessibilityInformation'] ?? null),
                'arePetsPermitted' => self::nullableBool($row['arePetsPermitted'] ?? null),
                'arePetsPermittedWithRestrictions' => self::nullableBool($row['arePetsPermittedWithRestrictions'] ?? null),
                'doFeesApply' => self::nullableBool($row['doFeesApply'] ?? null),
                'feeDescription' => self::nullableString($row['feeDescription'] ?? null),
                'isReservationRequired' => self::nullableBool($row['isReservationRequired'] ?? null),
                'reservationDescription' => self::nullableString($row['reservationDescription'] ?? null),
                'activities' => self::namesFromList($row['activities'] ?? null),
                'topics' => self::namesFromList($row['topics'] ?? null),
                'location' => self::nullableString($row['location'] ?? null),
                'locationDescription' => self::nullableString($row['locationDescription'] ?? null),
            ], static fn (mixed $v): bool => $v !== null && $v !== []),
            PoiKind::VisitorCenter => array_filter([
                'directionsInfo' => self::nullableString($row['directionsInfo'] ?? null),
                'directionsUrl' => self::nullableString($row['directionsUrl'] ?? null),
                'audioDescription' => self::nullableString($row['audioDescription'] ?? null),
                'passportStampLocationDescription' => self::nullableString($row['passportStampLocationDescription'] ?? null),
            ], static fn (mixed $v): bool => $v !== null),
            PoiKind::Campground => array_filter([
                'directionsOverview' => self::nullableString($row['directionsOverview'] ?? null),
                'directionsUrl' => self::nullableString($row['directionsUrl'] ?? null),
                'regulationsOverview' => self::nullableString($row['regulationsOverview'] ?? null),
                'regulationsUrl' => self::nullableString($row['regulationsurl'] ?? null),
                'reservationInfo' => self::nullableString($row['reservationInfo'] ?? null),
                'reservationUrl' => self::nullableString($row['reservationUrl'] ?? null),
                'weatherOverview' => self::nullableString($row['weatherOverview'] ?? null),
                'numberOfSitesReservable' => self::nullableInt($row['numberOfSitesReservable'] ?? null),
                'numberOfSitesFirstComeFirstServe' => self::nullableInt($row['numberOfSitesFirstComeFirstServe'] ?? null),
                'campsites' => self::asArrayOrNull($row['campsites'] ?? null),
                'accessibility' => self::asArrayOrNull($row['accessibility'] ?? null),
            ], static fn (mixed $v): bool => $v !== null),
        };
    }

    /** @return list<string> */
    protected static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $entry) {
            $str = trim((string) $entry);
            if ($str !== '') {
                $out[] = $str;
            }
        }

        return $out;
    }

    /** @return list<string> */
    protected static function namesFromList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $out[] = $name;
            }
        }

        return $out;
    }

    /** @return list<array<string, mixed>>|null */
    protected static function asListOrNull(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        return array_values(array_filter($value, static fn (mixed $v): bool => is_array($v)));
    }

    /** @return array<string, mixed>|null */
    protected static function asArrayOrNull(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }

    protected static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    protected static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    protected static function nullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => null,
        };
    }

    protected static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }
}
