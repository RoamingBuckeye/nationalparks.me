<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

use App\Integrations\Nps\Enums\AddressType;

final readonly class AddressData
{
    public function __construct(
        public ?AddressType $type,
        public ?string $line1,
        public ?string $line2,
        public ?string $line3,
        public ?string $city,
        public ?string $stateCode,
        public ?string $postalCode,
        public ?string $countryCode,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            type: AddressType::tryFrom((string) ($row['type'] ?? '')),
            line1: self::nullableString($row['line1'] ?? null),
            line2: self::nullableString($row['line2'] ?? null),
            line3: self::nullableString($row['line3'] ?? null),
            city: self::nullableString($row['city'] ?? null),
            stateCode: self::nullableString($row['stateCode'] ?? null),
            postalCode: self::nullableString($row['postalCode'] ?? null),
            countryCode: self::nullableString($row['countryCode'] ?? null),
        );
    }

    /**
     * @param  list<array<string, mixed>>|null  $rows
     * @return list<self>
     */
    public static function listFrom(?array $rows): array
    {
        if ($rows === null) {
            return [];
        }

        return array_map(
            fn (array $row): self => self::fromArray($row),
            $rows,
        );
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
