<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

use App\Integrations\Nps\Enums\FeeKind;

final readonly class ParkData
{
    /**
     * @param  list<string>  $states
     * @param  list<string>  $activities
     * @param  list<string>  $topics
     * @param  list<ImageData>  $images
     * @param  list<AddressData>  $addresses
     * @param  list<ContactData>  $contacts
     * @param  list<EntranceFeeData>  $fees
     * @param  list<OperatingHoursData>  $operatingHours
     */
    public function __construct(
        public string $npsId,
        public string $parkCode,
        public string $name,
        public string $fullName,
        public string $designation,
        public string $description,
        public ?float $latitude,
        public ?float $longitude,
        public array $states,
        public string $url,
        public ?string $directionsInfo,
        public ?string $directionsUrl,
        public ?string $weatherInfo,
        public array $activities,
        public array $topics,
        public array $images,
        public array $addresses,
        public array $contacts,
        public array $fees,
        public array $operatingHours,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            npsId: (string) ($row['id'] ?? ''),
            parkCode: (string) ($row['parkCode'] ?? ''),
            name: (string) ($row['name'] ?? ''),
            fullName: (string) ($row['fullName'] ?? ''),
            designation: (string) ($row['designation'] ?? ''),
            description: (string) ($row['description'] ?? ''),
            latitude: self::nullableFloat($row['latitude'] ?? null),
            longitude: self::nullableFloat($row['longitude'] ?? null),
            states: self::statesFromCsv((string) ($row['states'] ?? '')),
            url: (string) ($row['url'] ?? ''),
            directionsInfo: self::nullableString($row['directionsInfo'] ?? null),
            directionsUrl: self::nullableString($row['directionsUrl'] ?? null),
            weatherInfo: self::nullableString($row['weatherInfo'] ?? null),
            activities: self::namesFromList($row['activities'] ?? null),
            topics: self::namesFromList($row['topics'] ?? null),
            images: ImageData::listFrom($row['images'] ?? null),
            addresses: AddressData::listFrom($row['addresses'] ?? null),
            contacts: ContactData::listFrom(self::asArrayOrNull($row['contacts'] ?? null)),
            fees: [
                ...EntranceFeeData::listFrom($row['entranceFees'] ?? null, FeeKind::Entrance),
                ...EntranceFeeData::listFrom($row['entrancePasses'] ?? null, FeeKind::Pass),
            ],
            operatingHours: OperatingHoursData::listFrom($row['operatingHours'] ?? null),
        );
    }

    /** @return list<string> */
    protected static function statesFromCsv(string $csv): array
    {
        if ($csv === '') {
            return [];
        }

        $parts = array_filter(array_map('trim', explode(',', $csv)), static fn (string $s): bool => $s !== '');

        return array_values($parts);
    }

    /**
     * @param  list<array<string, mixed>>|null  $rows
     * @return list<string>
     */
    protected static function namesFromList(?array $rows): array
    {
        if ($rows === null) {
            return [];
        }

        $names = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    protected static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    protected static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }

    /** @return array<string, mixed>|null */
    protected static function asArrayOrNull(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }
}
